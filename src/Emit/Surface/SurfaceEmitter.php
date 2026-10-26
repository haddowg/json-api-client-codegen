<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

use haddowg\JsonApiCodegen\Descriptor\ActionScope;
use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * The operation surface of every type: the type accessor (`Albums`), the id handle
 * (`AlbumHandle`), one relationship handle per relation (`AlbumArtistRelation`), the pivot edge
 * builders, and the custom-action classes (`AlbumActions`, `AlbumCollectionActions`).
 *
 * Every method exists because the document advertises its operation. A suppressed endpoint, an
 * unadvertised verb, or a write with no projection is an absent method or parameter, never a
 * guard that throws at runtime. Each method's `@throws` list is built from its own operation's
 * declared error statuses.
 *
 * None of these classes carries a property hook, so the 8.3 and 8.4 targets emit them alike.
 */
final class SurfaceEmitter implements Emitter
{
    /** Methods the id handle declares itself, which a relation method must not shadow. */
    private const array HANDLE_METHODS = ['__construct', 'id', 'get', 'update', 'delete', 'actions'];

    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        foreach ($api->resources as $resource) {
            $type = new TypeSurface($api, $resource);

            $relations = [];
            foreach ($resource->relations as $relation) {
                $units = RelationSource::units($type, $relation);
                if ($units !== []) {
                    $relations[$relation->name] = $units[0]->name;
                    yield from $units;
                }
            }

            $resourceActions = ActionSource::unit($type, ActionScope::Resource);
            $collectionActions = ActionSource::unit($type, ActionScope::Collection);
            if ($resourceActions !== null) {
                yield $resourceActions;
            }
            if ($collectionActions !== null) {
                yield $collectionActions;
            }

            $handle = self::handle($type, $relations, $resourceActions !== null);
            if ($handle !== null) {
                yield $handle;
            }

            if (self::exposed($resource)) {
                yield self::accessor($type, $handle !== null, $collectionActions !== null);
            }
        }
    }

    /**
     * Whether a type has an endpoint of any kind. A type the document describes only as a schema
     * has nothing to reach, so it gets no accessor and no client property.
     */
    public static function exposed(ResourceDescriptor $resource): bool
    {
        if ($resource->operations !== [] || $resource->actions !== []) {
            return true;
        }
        foreach ($resource->relations as $relation) {
            if ($relation->relatedRead !== null || $relation->relationshipRead !== null || $relation->mutations !== []) {
                return true;
            }
        }

        return false;
    }

    private static function accessor(TypeSurface $type, bool $hasHandle, bool $hasActions): ClassUnit
    {
        $imports = new Imports();
        $fetcher = $imports->runtime('Resources\\Fetcher');
        $members = ['    public function __construct(private readonly ' . $fetcher . " \$fetcher) {}\n"];
        $classDoc = [];

        if ($type->operation(OperationKind::FetchMany) !== null) {
            $query = $type->named('Query');
            $paginator = $type->resource->paginator;
            $generic = $query . '<' . $type->base() . '>';
            $arguments = '$this->fetcher';
            $prose = ['A read of the ' . $type->type() . ' collection, not yet sent: unprojected, unfiltered and unsorted.'];
            if ($paginator->kind === PaginatorKind::Multi) {
                $page = $imports->runtime('Pagination\\' . (TypeSurface::pageClass($paginator) ?? 'Page'));
                $generic = $query . '<' . $type->base() . ', ' . $page . '>';
                $arguments .= ', ' . $page . '::read(...)';
                $prose = Php::wrap($prose[0] . ' Until a picker chooses a strategy, it reads the server\'s default, `' . ($paginator->default ?? '') . '`.', 4);
            }
            $members[] = Php::docblock([
                ...$prose,
                '',
                '@return ' . $generic,
            ], 4, false)
                . '    public function query(): ' . $query . "\n    {\n"
                . '        /** @var ' . $generic . " */\n"
                . '        return new ' . $query . '(' . $arguments . ");\n    }\n";
        }

        if ($hasHandle) {
            $handle = $type->named('Handle');
            $members[] = Php::docblock(Php::wrap('A handle on one ' . $type->label() . ' by id. Nothing is fetched until a method on it is called.', 4), 4, false)
                . '    public function id(string $id): ' . $handle . "\n    {\n"
                . '        return new ' . $handle . "(\$this->fetcher, \$id);\n    }\n";
        }

        $create = $type->operation(OperationKind::Create);
        if ($create !== null) {
            $classDoc[] = TypeSurface::importAlias($type->type(), 'CreateShape');
            $members[] = self::create($type, $imports);
        }

        if ($hasActions) {
            $actions = $type->named('CollectionActions');
            $members[] = Php::docblock(Php::wrap('Collection-scoped custom actions, behind their own namespace because an action name may collide with a relation or a verb.', 4), 4, false)
                . '    public function actions(): ' . $actions . "\n    {\n"
                . '        return new ' . $actions . "(\$this->fetcher);\n    }\n";
        }

        $doc = [
            ...Php::wrap('The `' . $type->type() . '` type: every operation its collection endpoint advertises, and the way to one resource by id.'),
            '',
        ];
        if ($classDoc !== []) {
            $doc = [...$doc, ...$classDoc, ''];
        }
        $doc[] = '@generated from the `' . $type->type() . '` operations';

        return new ClassUnit(
            Naming::accessor($type->type()),
            UnitKind::FinalClass,
            $type->file(),
            self::declaration($doc, Naming::accessor($type->type()), $members),
            $imports->all(),
        );
    }

    private static function create(TypeSurface $type, Imports $imports): string
    {
        $create = $type->operation(OperationKind::Create);
        \assert($create !== null);
        $projected = $create->projection !== null;
        $resource = $type->returnsResource($create);
        $base = $type->base();
        $projection = $type->named('Projection');
        $imports->runtime('Http\\Request');

        $params = [[TypeSurface::createDocType($type->type()), '$input']];
        $signature = [TypeSurface::createNativeType($type->type()) . ' $input'];
        if ($projected) {
            $params[] = [TypeSurface::projectionParam($projection, $base), '$projection'];
            $signature[] = 'callable|' . $projection . '|null $projection = null';
        }

        $doc = Php::wrap(match (true) {
            !$resource => 'Create ' . self::article($type->label()) . ', in any of the three input forms. The server accepts the create for processing and answers with something other than the ' . $type->label() . ', so the response is returned as it came.',
            $projected => 'Create ' . self::article($type->label()) . ', in any of the three input forms, and return it shaped by the optional projection: a closure over a fresh projection, or a prebuilt one.',
            default => 'Create ' . self::article($type->label()) . ', in any of the three input forms, and return it.',
        }, 4);
        // A client-generated id is a member of the create input, so all three forms carry it.
        $clientId = match ($type->resource->clientId) {
            ClientIdPolicy::Forbidden => null,
            ClientIdPolicy::Optional => 'The document accepts a client-generated id, carried as the input\'s `id`.',
            ClientIdPolicy::Required => 'The document requires a client-generated id, carried as the input\'s `id`.',
        };
        if ($clientId !== null) {
            $doc = [...$doc, '', ...Php::wrap($clientId, 4)];
        }
        $doc[] = '';
        if ($projected) {
            $doc = [...$doc, '@template TProj of object', ''];
        }
        $doc = [...$doc, ...TypeSurface::params($params), ''];
        if ($projected) {
            $doc = [...$doc, '@return ($projection is null ? ' . $base . ' : ' . $base . '&TProj)', ''];
        }
        $doc = [...$doc, ...Throws::tags($create->errorStatuses, $imports, [
            'Forbidden' => 'when the create is not permitted',
        ], $resource)];

        $json = '\\json_encode($document->toDocument(), \\JSON_THROW_ON_ERROR)';
        $path = Php::string($create->path);
        $body = TypeSurface::createInput($type->type()) . "\n";

        if (!$resource) {
            $return = $imports->runtime('Http\\Response');
            $body .= '        return $this->fetcher->send(' . TypeSurface::request($create->method, $path, $json) . ");\n";
        } elseif ($projected) {
            $return = $base;
            $imports->runtime('Exceptions\\MalformedDocument');
            $body .= '        $query = ' . $projection . "::resolve(\$projection);\n\n"
                . "        \$context = \$this->fetcher->read(\n"
                . '            ' . TypeSurface::request($create->method, '$query->appendTo(' . $path . ')', $json) . ",\n"
                . "            \$query,\n"
                . "        );\n\n"
                . TypeSurface::hydratePrimary($type->concrete(), $base, 'create', true);
        } else {
            $return = $base;
            $imports->runtime('Exceptions\\MalformedDocument');
            $body .= '        $context = $this->fetcher->read(' . TypeSurface::request($create->method, $path, $json) . ");\n"
                . TypeSurface::hydratePrimary($type->concrete(), $base, 'create', false);
        }

        return Php::docblock($doc, 4, false)
            . self::signature('create', $signature, $return)
            . $body
            . "    }\n";
    }

    /**
     * @param array<string, string> $relations relation name => its relationship handle class
     */
    private static function handle(TypeSurface $type, array $relations, bool $hasActions): ?ClassUnit
    {
        $fetchOne = $type->operation(OperationKind::FetchOne);
        $update = $type->operation(OperationKind::Update);
        $delete = $type->operation(OperationKind::Delete);
        if ($fetchOne === null && $update === null && $delete === null && $relations === [] && !$hasActions) {
            return null;
        }

        $imports = new Imports();
        $fetcher = $imports->runtime('Resources\\Fetcher');
        $members = [
            "    public function __construct(\n"
            . '        private readonly ' . $fetcher . " \$fetcher,\n"
            . "        private readonly string \$id,\n"
            . "    ) {}\n",
            Php::docblock(['The id this handle addresses.'], 4, false)
            . "    public function id(): string\n    {\n        return \$this->id;\n    }\n",
        ];
        $classDoc = [];

        if ($fetchOne !== null) {
            $members[] = self::get($type, $imports);
        }
        if ($update !== null) {
            $classDoc[] = TypeSurface::importAlias($type->type(), 'UpdateShape');
            $members[] = self::update($type, $imports);
        }
        if ($delete !== null) {
            $imports->runtime('Http\\Request');
            $members[] = Php::docblock([
                ...Php::wrap('Delete the ' . $type->label() . '.', 4),
                '',
                ...Throws::tags($delete->errorStatuses, $imports, [
                    'Forbidden' => 'when the delete is not permitted',
                    'NotFound' => 'when no ' . $type->label() . ' has this id',
                ]),
            ], 4, false)
                . "    public function delete(): void\n    {\n"
                . '        $this->fetcher->send(' . TypeSurface::request($delete->method, TypeSurface::path($delete->path)) . ");\n"
                . "    }\n";
        }

        $taken = self::HANDLE_METHODS;
        $methods = [];
        foreach ($relations as $name => $class) {
            $method = Naming::member($name);
            if (\in_array(\strtolower($method), $taken, true)) {
                $method .= 'Relation';
            }
            if (\in_array(\strtolower($method), $taken, true) || isset($methods[\strtolower($method)])) {
                throw new \LogicException(\sprintf('The relation "%s" of "%s" would be the handle method %s(), which another member already is.', $name, $type->type(), $method));
            }
            $methods[\strtolower($method)] = true;
            $relation = $type->resource->relations[$name];
            $members[] = Php::docblock(Php::wrap(RelationSource::summary($relation), 4), 4, false)
                . '    public function ' . $method . '(): ' . $class . "\n    {\n"
                . '        return new ' . $class . "(\$this->fetcher, \$this->id);\n    }\n";
        }

        if ($hasActions) {
            $actions = $type->named('Actions');
            $members[] = Php::docblock(Php::wrap('Resource-scoped custom actions, behind their own namespace because an action name may collide with a relation or a verb.', 4), 4, false)
                . '    public function actions(): ' . $actions . "\n    {\n"
                . '        return new ' . $actions . "(\$this->fetcher, \$this->id);\n    }\n";
        }

        $doc = [
            ...Php::wrap('One ' . $type->label() . ' by id, before anything has been fetched. Creating the handle performs no IO; each method on it is one request.'),
            '',
        ];
        if ($classDoc !== []) {
            $doc = [...$doc, ...$classDoc, ''];
        }
        $doc[] = '@generated from the `' . $type->resource->type . '` operations on one resource';

        return new ClassUnit(
            $type->named('Handle'),
            UnitKind::FinalClass,
            $type->file(),
            self::declaration($doc, $type->named('Handle'), $members),
            $imports->all(),
        );
    }

    private static function get(TypeSurface $type, Imports $imports): string
    {
        $fetchOne = $type->operation(OperationKind::FetchOne);
        \assert($fetchOne !== null);
        $base = $type->base();
        $projection = $type->named('Projection');
        $request = $imports->runtime('Http\\Request');
        $imports->runtime('Exceptions\\MalformedDocument');
        $path = TypeSurface::path($fetchOne->path);
        $throws = Throws::tags($fetchOne->errorStatuses, $imports, [
            'BadRequest' => 'when a query parameter is rejected',
            'Forbidden' => 'when the read is not permitted',
            'NotFound' => 'when no ' . $type->label() . ' has this id',
        ], true);

        if ($fetchOne->projection === null) {
            return Php::docblock([...Php::wrap('Fetch the ' . $type->label() . '. The endpoint advertises no `include` or `fields`, so there is nothing to project.', 4), '', ...$throws], 4, false)
                . '    public function get(): ' . $base . "\n    {\n"
                . '        $context = $this->fetcher->read(' . $request . '::get(' . $path . "));\n"
                . TypeSurface::hydratePrimary($type->concrete(), $base, $type->type(), false)
                . "    }\n";
        }

        return Php::docblock([
            ...Php::wrap('Fetch the ' . $type->label() . ', shaped by the optional projection: a closure over a fresh projection, or a prebuilt one.', 4),
            '',
            '@template TProj of object',
            '',
            ...TypeSurface::params([[TypeSurface::projectionParam($projection, $base), '$projection']]),
            '',
            '@return ($projection is null ? ' . $base . ' : ' . $base . '&TProj)',
            '',
            ...$throws,
        ], 4, false)
            . '    public function get(callable|' . $projection . '|null $projection = null): ' . $base . "\n    {\n"
            . '        $query = ' . $projection . "::resolve(\$projection);\n"
            . '        $context = $this->fetcher->read(' . $request . '::get($query->appendTo(' . $path . ")), \$query);\n"
            . TypeSurface::hydratePrimary($type->concrete(), $base, $type->type(), true)
            . "    }\n";
    }

    private static function update(TypeSurface $type, Imports $imports): string
    {
        $update = $type->operation(OperationKind::Update);
        \assert($update !== null);
        $projected = $update->projection !== null;
        $resource = $type->returnsResource($update);
        $base = $type->base();
        $projection = $type->named('Projection');
        $updateClass = $type->named('Update');
        $imports->runtime('Http\\Request');
        $path = TypeSurface::path($update->path);
        $json = '\\json_encode($document->toDocument($this->id), \\JSON_THROW_ON_ERROR)';

        $params = [[$updateClass . '|' . Naming::alias($type->type(), 'UpdateShape'), '$input']];
        $signature = [$updateClass . '|array $input'];
        if ($projected) {
            $params[] = [TypeSurface::projectionParam($projection, $base), '$projection'];
            $signature[] = 'callable|' . $projection . '|null $projection = null';
        }

        $doc = [
            ...Php::wrap(match (true) {
                !$resource => 'Patch the ' . $type->label() . ', in either input form. The server answers with something other than the ' . $type->label() . ', so the response is returned as it came.',
                $projected => 'Patch the ' . $type->label() . ', in either input form, and return it shaped by the optional projection.',
                default => 'Patch the ' . $type->label() . ', in either input form, and return it.',
            }, 4),
            '',
            ...Php::wrap('The id comes from the handle and is written into the document, so the body and the URL cannot disagree.', 4),
            '',
        ];
        if ($projected) {
            $doc = [...$doc, '@template TProj of object', ''];
        }
        $doc = [...$doc, ...TypeSurface::params($params), ''];
        if ($projected) {
            $doc = [...$doc, '@return ($projection is null ? ' . $base . ' : ' . $base . '&TProj)', ''];
        }
        $doc = [...$doc, ...Throws::tags($update->errorStatuses, $imports, [
            'Forbidden' => 'when the update is not permitted',
            'NotFound' => 'when no ' . $type->label() . ' has this id',
            'Conflict' => "when the document's type or id disagrees with the endpoint",
        ], $resource)];

        $body = TypeSurface::updateInput($type->type()) . "\n";
        if (!$resource) {
            $return = $imports->runtime('Http\\Response');
            $body .= '        return $this->fetcher->send(' . TypeSurface::request($update->method, $path, $json, 8) . ");\n";
        } elseif ($projected) {
            $return = $base;
            $imports->runtime('Exceptions\\MalformedDocument');
            $body .= '        $query = ' . $projection . "::resolve(\$projection);\n\n"
                . "        \$context = \$this->fetcher->read(\n"
                . '            ' . TypeSurface::request($update->method, '$query->appendTo(' . $path . ')', $json, 12) . ",\n"
                . "            \$query,\n"
                . "        );\n\n"
                . TypeSurface::hydratePrimary($type->concrete(), $base, 'update', true);
        } else {
            $return = $base;
            $imports->runtime('Exceptions\\MalformedDocument');
            $body .= '        $context = $this->fetcher->read(' . TypeSurface::request($update->method, $path, $json, 8) . ");\n"
                . TypeSurface::hydratePrimary($type->concrete(), $base, 'update', false);
        }

        return Php::docblock($doc, 4, false)
            . self::signature('update', $signature, $return)
            . $body
            . "    }\n";
    }

    /**
     * A method's opening: one parameter on the signature line, several one per line.
     *
     * @param list<string> $params
     */
    public static function signature(string $name, array $params, string $return): string
    {
        if (\count($params) <= 1) {
            return '    public function ' . $name . '(' . \implode('', $params) . '): ' . $return . "\n    {\n";
        }

        return '    public function ' . $name . "(\n"
            . \implode('', \array_map(static fn(string $param): string => '        ' . $param . ",\n", $params))
            . '    ): ' . $return . " {\n";
    }

    /**
     * A final class from its docblock lines and member sources.
     *
     * @param list<string> $doc
     * @param list<string> $members
     */
    public static function declaration(array $doc, string $name, array $members): string
    {
        return Php::docblock($doc, 0, false)
            . 'final class ' . $name . "\n{\n"
            . \implode("\n", $members)
            . "}\n";
    }

    private static function article(string $noun): string
    {
        return (\in_array($noun[0] ?? '', ['a', 'e', 'i', 'o', 'u'], true) ? 'an ' : 'a ') . $noun;
    }
}
