<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PivotFieldDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RelationVerb;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * One relationship handle per relation (`AlbumTracksRelation`), and the edge builder of a pivot
 * relation whose pivot carries a writable field (`PlaylistOrderedTracksEdge`).
 *
 * The handle exposes exactly the endpoints the document advertises for the relation. The related
 * read and the linkage read are independent: either can be switched off server-side, so each is
 * generated only where its own endpoint exists. The write verbs follow the relationship
 * endpoint's methods, so a to-one gets `set()` and a to-many whichever of `add()`, `remove()`
 * and `replace()` it declares.
 */
final class RelationSource
{
    /** Local names a member variable must not take. */
    private const array RESERVED_VARIABLES = ['this', 'document', 'context', 'raw', 'url', 'identifiers', 'pivot', 'identifier'];

    private const array READ_REASONS = [
        'BadRequest' => 'when a query parameter is rejected',
        'Forbidden' => 'when the read is not permitted',
    ];

    private const array WRITE_REASONS = [
        'BadRequest' => 'when the document is malformed',
        'Forbidden' => 'when the write is not permitted',
        'Conflict' => 'when the linkage conflicts with the relationship',
        'ValidationFailed' => 'when the linkage fails validation',
    ];

    /**
     * The handle first, then the edge builder when there is one. Empty when the relation exposes
     * no endpoint at all.
     *
     * @return list<ClassUnit>
     */
    public static function units(TypeSurface $type, RelationDescriptor $relation): array
    {
        if (!$relation->related() && !$relation->relationship() && $relation->mutations === []) {
            return [];
        }

        $edge = self::edgeFields($relation) === [] ? null : self::edge($type, $relation);
        $units = [self::handle($type, $relation, $edge?->name)];
        if ($edge !== null) {
            $units[] = $edge;
        }

        return $units;
    }

    /** One line on the id handle's accessor for this relation. */
    public static function summary(RelationDescriptor $relation): string
    {
        $verbs = \array_map(static fn(RelationVerb $verb): string => '`' . $verb->value . '()`', $relation->verbs());
        $cardinality = $relation->cardinality === Cardinality::One ? 'a to-one' : 'a to-many';

        return 'The `' . $relation->name . '` relationship, ' . $cardinality . ($verbs === []
            ? ' the document advertises no write for.'
            : ' whose endpoint advertises ' . self::list($verbs) . '.');
    }

    private static function handle(TypeSurface $type, RelationDescriptor $relation, ?string $edge): ClassUnit
    {
        $imports = new Imports();
        $name = Naming::relationHandle($type->type(), $relation->name);
        $fetcher = $imports->runtime('Resources\\Fetcher');
        $toOne = $relation->cardinality === Cardinality::One;
        $classes = self::relatedClasses($type, $relation);
        $notFound = ['NotFound' => 'when no ' . $type->label() . ' has this id'];

        $members = [
            "    public function __construct(\n"
            . '        private readonly ' . $fetcher . " \$fetcher,\n"
            . "        private readonly string \$id,\n"
            . "    ) {}\n",
        ];

        $page = null;
        if (!$toOne) {
            $page = TypeSurface::pageClass($type->relationPaginator($relation->paginator, $relation->types[0] ?? ''));
        }

        if ($relation->relationshipRead !== null) {
            $members[] = $toOne
                ? self::linkageOne($relation, $relation->relationshipRead, $imports, $notFound)
                : self::linkageMany($relation, $relation->relationshipRead, $imports, $notFound, $page);
        }
        if ($relation->relatedRead !== null) {
            if ($classes === []) {
                throw new \LogicException(\sprintf('The relation "%s" of "%s" has a related read, but the document describes none of its types (%s).', $relation->name, $type->type(), \implode(', ', $relation->types)));
            }
            $members[] = $toOne
                ? self::relatedOne($relation, $relation->relatedRead, $imports, $notFound, $classes)
                : self::relatedMany($relation, $relation->relatedRead, $imports, $notFound, $classes, $page);
        }

        $verbs = $toOne ? [RelationVerb::Set] : [RelationVerb::Add, RelationVerb::Remove, RelationVerb::Replace];
        foreach ($verbs as $verb) {
            $operation = $relation->mutation($verb);
            if ($operation !== null) {
                $members[] = self::mutation($relation, $verb, $operation, $imports, $notFound, $classes, $verb === RelationVerb::Remove ? null : $edge);
            }
        }

        if ($relation->relationshipRead !== null && !$toOne) {
            $members[] = self::linkageCollector($relation, $imports, $page);
        }
        if ($relation->relatedRead !== null && !$toOne) {
            $members[] = self::resourceCollector($relation, $imports, $classes, $page);
        }
        if ($edge !== null && ($relation->permits(RelationVerb::Add) || $relation->permits(RelationVerb::Replace))) {
            $members[] = self::edgesHelper($edge);
        }
        if (\count($classes) > 1 && $relation->relatedRead !== null) {
            $members[] = self::dispatcher($relation, $imports, $classes);
        }

        $endpoints = [];
        if ($relation->relatedRead !== null) {
            $endpoints[] = '`' . $relation->relatedRead->path . '`';
        }
        if ($relation->relationshipRead !== null || $relation->mutations !== []) {
            $endpoints[] = '`' . ($relation->relationshipRead ?? \array_values($relation->mutations)[0])->path . '`';
        }

        $doc = [...Php::wrap(self::description($type, $relation, $edge)), '', '@generated from ' . \implode(' and ', $endpoints)];

        return new ClassUnit($name, UnitKind::FinalClass, $type->file(), SurfaceEmitter::declaration($doc, $name, $members), $imports->all());
    }

    private static function description(TypeSurface $type, RelationDescriptor $relation, ?string $edge): string
    {
        $reads = [];
        if ($relation->relationshipRead !== null) {
            $reads[] = '`get()` reads the linkage';
        }
        if ($relation->relatedRead !== null) {
            $reads[] = '`related()` reads the related resources';
        }
        $text = 'The `' . $relation->name . '` relationship of one ' . $type->label() . '. '
            . ($reads === [] ? 'The document advertises no read for it.' : TypeSurface::sentence(self::list($reads)) . '; any read the server switched off is absent.');
        $verbs = \array_map(static fn(RelationVerb $verb): string => '`' . $verb->value . '()`', $relation->verbs());
        $text .= ' ' . ($verbs === []
            ? 'No write is advertised, so none is generated.'
            : 'The writes are ' . self::list($verbs) . ', the methods the relationship endpoint declares, and no others.');
        if ($edge !== null) {
            $text .= ' Members are written as {@see ' . $edge . '}, which carries the writable pivot fields.';
        }

        return $text;
    }

    /**
     * @param array<string, string> $notFound
     */
    private static function linkageOne(RelationDescriptor $relation, OperationDescriptor $read, Imports $imports, array $notFound): string
    {
        $identifier = $imports->runtime('Support\\Identifier');
        $request = $imports->runtime('Http\\Request');

        return Php::docblock([
            ...Php::wrap('The linkage alone: the identifier of the related resource, or null, without fetching it.', 4),
            '',
            ...Throws::tags($read->errorStatuses, $imports, [...self::READ_REASONS, ...$notFound]),
        ], 4, false)
            . '    public function get(): ?' . $identifier . "\n    {\n"
            . "        \$raw = \$this->fetcher\n"
            . '            ->read(' . $request . '::get(' . TypeSurface::path($read->path) . "))\n"
            . "            ->primary();\n\n"
            . '        return $raw === null ? null : ' . $identifier . '::fromArray($raw, ' . Php::string('the "' . $relation->name . '" linkage') . ");\n"
            . "    }\n";
    }

    /**
     * @param array<string, string> $notFound
     */
    private static function linkageMany(RelationDescriptor $relation, OperationDescriptor $read, Imports $imports, array $notFound, ?string $page): string
    {
        $imports->runtime('Http\\Request');

        return Php::docblock([
            ...Php::wrap($page === null ? 'The linkage alone, without fetching the related resources.' : 'The linkage alone, a page at a time, without fetching the related resources.', 4),
            '',
            '@return ' . self::collectionType($imports, 'Identifier', $page),
            '',
            ...Throws::tags($read->errorStatuses, $imports, [...self::READ_REASONS, ...$notFound]),
        ], 4, false)
            . '    public function get(): ' . self::collectionClass($imports, $page) . "\n    {\n"
            . "        return \$this->linkage(\n"
            . '            $this->fetcher->read(' . TypeSurface::request('GET', TypeSurface::path($read->path)) . "),\n"
            . "        );\n"
            . "    }\n";
    }

    /**
     * @param array<string, string> $notFound
     * @param list<string>          $classes
     */
    private static function relatedOne(RelationDescriptor $relation, OperationDescriptor $read, Imports $imports, array $notFound, array $classes): string
    {
        $imports->runtime('Http\\Request');
        $hydrate = \count($classes) === 1 ? $classes[0] . '::_hydrate($raw, $context)' : 'self::member($raw, $context)';

        return Php::docblock([
            ...Php::wrap('The related resource itself, or null when the relationship is empty.', 4),
            '',
            ...Throws::tags($read->errorStatuses, $imports, [...self::READ_REASONS, ...$notFound]),
        ], 4, false)
            . '    public function related(): ' . (\count($classes) === 1 ? '?' . $classes[0] : self::union($classes) . '|null') . "\n    {\n"
            . '        $context = $this->fetcher->read(' . TypeSurface::request('GET', TypeSurface::path($read->path)) . ");\n"
            . "        \$raw = \$context->primary();\n\n"
            . '        return $raw === null ? null : ' . $hydrate . ";\n"
            . "    }\n";
    }

    /**
     * @param array<string, string> $notFound
     * @param list<string>          $classes
     */
    private static function relatedMany(RelationDescriptor $relation, OperationDescriptor $read, Imports $imports, array $notFound, array $classes, ?string $page): string
    {
        $imports->runtime('Http\\Request');

        return Php::docblock([
            ...Php::wrap($page === null ? 'The related resources themselves.' : 'The related resources themselves, a page at a time.', 4),
            '',
            '@return ' . self::collectionType($imports, self::union($classes), $page),
            '',
            ...Throws::tags($read->errorStatuses, $imports, [...self::READ_REASONS, ...$notFound]),
        ], 4, false)
            . '    public function related(): ' . self::collectionClass($imports, $page) . "\n    {\n"
            . '        return $this->resources($this->fetcher->read(' . TypeSurface::request('GET', TypeSurface::path($read->path)) . "));\n"
            . "    }\n";
    }

    /**
     * @param array<string, string> $notFound
     * @param list<string>          $classes
     */
    private static function mutation(RelationDescriptor $relation, RelationVerb $verb, OperationDescriptor $operation, Imports $imports, array $notFound, array $classes, ?string $edge): string
    {
        $identifier = $imports->runtime('Support\\Identifier');
        $coerce = $imports->runtime('Support\\Coerce');
        $imports->runtime('Http\\Request');
        $members = self::union([...$classes, $identifier]);
        $variable = self::variable($relation, 'targets');

        if ($verb === RelationVerb::Set) {
            $param = null;
            $signature = $members . '|null $target';
            $data = $coerce . '::linkage($target)';
            $summary = 'Point the relationship at another resource, or at nothing.';
        } else {
            $memberType = $edge ?? $members;
            $param = '@param list<' . $memberType . '> $' . $variable;
            $signature = 'array $' . $variable;
            $data = $edge === null ? $coerce . '::linkageList($' . $variable . ')' : 'self::edges($' . $variable . ')';
            $summary = match ($verb) {
                RelationVerb::Add => 'Add members to the relationship, leaving the rest alone.',
                RelationVerb::Remove => 'Remove members from the relationship, leaving the rest alone.',
                default => 'Replace every member of the relationship.',
            };
        }

        return Php::docblock([
            ...Php::wrap($summary, 4),
            '',
            ...($param === null ? [] : [$param, '']),
            ...Throws::tags($operation->errorStatuses, $imports, [...self::WRITE_REASONS, ...$notFound]),
        ], 4, false)
            . '    public function ' . $verb->value . '(' . $signature . "): void\n    {\n"
            . '        $this->fetcher->send(' . TypeSurface::request(
                $operation->method,
                TypeSurface::path($operation->path),
                "\\json_encode(['data' => " . $data . '], \\JSON_THROW_ON_ERROR)',
                8,
            ) . ");\n"
            . "    }\n";
    }

    private static function linkageCollector(RelationDescriptor $relation, Imports $imports, ?string $page): string
    {
        $context = $imports->runtime('Resources\\ResourceContext');
        $identifier = $imports->runtime('Support\\Identifier');
        $collect = "        foreach (\$context->primaryList() as \$raw) {\n"
            . '            $identifiers[] = ' . $identifier . '::fromArray($raw, ' . Php::string('the "' . $relation->name . '" linkage') . ");\n"
            . "        }\n\n";

        return self::collector('linkage', 'identifiers', 'Identifier', $collect, $context, $imports, $page, '$this->fetcher->readUrl($url)');
    }

    /**
     * @param list<string> $classes
     */
    private static function resourceCollector(RelationDescriptor $relation, Imports $imports, array $classes, ?string $page): string
    {
        $context = $imports->runtime('Resources\\ResourceContext');
        $variable = self::variable($relation, 'members');
        $hydrate = \count($classes) === 1 ? $classes[0] . '::_hydrate($raw, $context)' : 'self::member($raw, $context)';
        $collect = "        foreach (\$context->primaryList() as \$raw) {\n"
            . '            $' . $variable . '[] = ' . $hydrate . ";\n"
            . "        }\n\n";
        $readQuery = $imports->runtime('Query\\ReadQuery');

        return self::collector('resources', $variable, self::union($classes), $collect, $context, $imports, $page, '$this->fetcher->readUrl($url, new ' . $readQuery . '())');
    }

    /**
     * A private method turning one response into the relation's collection, following page
     * links back through itself.
     */
    private static function collector(string $method, string $variable, string $member, string $collect, string $context, Imports $imports, ?string $page, string $follow): string
    {
        $out = Php::docblock(['@return ' . self::collectionType($imports, $member, $page)], 4, false)
            . '    private function ' . $method . '(' . $context . ' $context): ' . self::collectionClass($imports, $page) . "\n    {\n"
            . "        \$document = \$context->document();\n"
            . '        $' . $variable . " = [];\n\n"
            . $collect;

        if ($page === null) {
            return $out
                . '        return new ' . self::collectionClass($imports, $page) . '($' . $variable . ", \$document->meta(), \$document->links());\n"
                . "    }\n";
        }

        $links = $imports->runtime('Pagination\\PageLinks');
        $collection = self::collectionClass($imports, $page);

        return $out
            . '        return new ' . $collection . "(\n"
            . '            $' . $variable . ",\n"
            . '            ' . $page . '::read($document->pageMeta(), ' . $links . "::fromArray(\$document->links())),\n"
            . "            \$document->meta(),\n"
            . "            \$document->links(),\n"
            . '            fn(string $url): ' . $collection . ' => $this->' . $method . '(' . $follow . "),\n"
            . "        );\n"
            . "    }\n";
    }

    private static function edgesHelper(string $edge): string
    {
        return Php::docblock([
            '@param list<' . $edge . '> $edges',
            '',
            '@return list<array{type: string, id: string, meta: array{pivot: array<string, mixed>}}>',
        ], 4, false)
            . "    private static function edges(array \$edges): array\n    {\n"
            . '        return \\array_map(static fn(' . $edge . " \$edge): array => \$edge->toLinkage(), \$edges);\n"
            . "    }\n";
    }

    /**
     * @param list<string> $classes
     */
    private static function dispatcher(RelationDescriptor $relation, Imports $imports, array $classes): string
    {
        $context = $imports->runtime('Resources\\ResourceContext');
        $malformed = $imports->runtime('Exceptions\\MalformedDocument');
        $arms = '';
        $types = [];
        foreach ($relation->types as $type) {
            $class = Naming::resource($type);
            if (!\in_array($class, $classes, true)) {
                continue;
            }
            $types[] = '"' . $type . '"';
            $arms .= '            ' . Php::string($type) . ' => ' . $class . "::_hydrate(\$raw, \$context),\n";
        }

        return Php::docblock([
            'The related resource as the class its own `type` names.',
            '',
            '@param array<string, mixed> $raw',
            '',
            '@throws ' . $malformed . ' when the resource is of a type the relation does not declare',
        ], 4, false)
            . '    private static function member(array $raw, ' . $context . ' $context): ' . self::union($classes) . "\n    {\n"
            . "        return match (\$raw['type'] ?? null) {\n"
            . $arms
            . '            default => throw ' . $malformed . "::unexpectedMember('The related resource', 'type', " . Php::string('one of ' . \implode(', ', $types)) . "),\n"
            . "        };\n"
            . "    }\n";
    }

    /**
     * The edge builder: the related resource plus every writable pivot field, required ones
     * without a default so omitting one is a static error.
     */
    private static function edge(TypeSurface $type, RelationDescriptor $relation): ClassUnit
    {
        $imports = new Imports();
        $name = Naming::perType($type->type(), Naming::pascal($relation->name) . 'Edge');
        $identifier = $imports->runtime('Support\\Identifier');
        $classes = self::relatedClasses($type, $relation);
        $fields = self::edgeFields($relation);

        $params = [];
        $constructor = ['        public readonly ' . self::union([...$classes, $identifier]) . " \$target,\n"];
        $required = '';
        $optional = '';
        foreach ($fields as $field) {
            $property = Naming::member($field->name);
            if ($property === 'target') {
                throw new \LogicException(\sprintf('The pivot field "%s" of "%s.%s" would collide with the edge\'s target.', $field->name, $type->type(), $relation->name));
            }
            [$native, $doc] = self::pivotType($field, $imports);
            $value = self::pivotValue($field, '$this->' . $property);
            if ($field->required) {
                $constructor[] = '        public readonly ' . $native . ' $' . $property . ",\n";
                $required .= '            ' . Php::string($field->name) . ' => ' . $value . ",\n";
                if ($doc !== null) {
                    $params[] = [$doc, '$' . $property];
                }
            } else {
                $missing = $imports->runtime('Support\\Missing');
                $constructor[] = '        public readonly ' . ($native === 'mixed' ? 'mixed' : self::withMissing($native, $missing)) . ' $' . $property . ' = ' . $missing . "::Value,\n";
                $optional .= '        if (!$this->' . $property . ' instanceof ' . $missing . ") {\n"
                    . '            $pivot[' . Php::string($field->name) . '] = ' . $value . ";\n"
                    . "        }\n\n";
                if ($doc !== null) {
                    $params[] = [self::withMissing($doc, $missing), '$' . $property];
                }
            }
        }

        $readOnly = \array_values(\array_map(
            static fn(PivotFieldDescriptor $field): string => '`' . $field->name . '`',
            \array_filter($relation->pivotFields, static fn(PivotFieldDescriptor $field): bool => $field->readOnly),
        ));
        $doc = [...Php::wrap(
            'One `' . $relation->name . '` member and its pivot data, for a relationship write. Only the writable pivot fields are here'
            . ($readOnly === [] ? '' : ' (' . self::list($readOnly) . ' ' . (\count($readOnly) === 1 ? 'is' : 'are') . ' read-only)')
            . ', and a required one takes no default, so leaving it out fails before the request exists.',
        ), '', '@generated from the `meta.pivot` of `' . $type->type() . '.' . $relation->name . '` linkage'];

        $constructorDoc = $params === [] ? '' : Php::docblock(TypeSurface::params($params), 4, false);
        $members = [
            $constructorDoc
            . "    public function __construct(\n"
            . \implode('', $constructor)
            . "    ) {}\n",
            Php::docblock([
                'The linkage member this edge writes, its pivot under `meta.pivot`.',
                '',
                '@return array{type: string, id: string, meta: array{pivot: array<string, mixed>}}',
            ], 4, false)
            . "    public function toLinkage(): array\n    {\n"
            . ($required === '' ? "        \$pivot = [];\n\n" : "        \$pivot = [\n" . $required . "        ];\n\n")
            . $optional
            . '        $identifier = $this->target instanceof ' . $identifier . " ? \$this->target : \$this->target->_identifier();\n\n"
            . "        return ['type' => \$identifier->type, 'id' => \$identifier->id, 'meta' => ['pivot' => \$pivot]];\n"
            . "    }\n",
        ];

        return new ClassUnit($name, UnitKind::FinalClass, $type->file(), SurfaceEmitter::declaration($doc, $name, $members), $imports->all());
    }

    /**
     * The writable pivot fields, required ones first.
     *
     * @return list<PivotFieldDescriptor>
     */
    private static function edgeFields(RelationDescriptor $relation): array
    {
        if (!$relation->pivot) {
            return [];
        }
        $writable = \array_values(\array_filter($relation->pivotFields, static fn(PivotFieldDescriptor $field): bool => !$field->readOnly));
        \usort($writable, static fn(PivotFieldDescriptor $a, PivotFieldDescriptor $b): int => [$a->required ? 0 : 1, $a->name] <=> [$b->required ? 0 : 1, $b->name]);

        return $writable;
    }

    /**
     * The native type of a pivot field, and its docblock type where the native one says less.
     *
     * @return array{string, string|null}
     */
    private static function pivotType(PivotFieldDescriptor $field, Imports $imports): array
    {
        [$native, $doc] = match (true) {
            $field->enum !== null => [$field->enum, null],
            \in_array($field->format, ['date-time', 'date'], true) => ['\\DateTimeImmutable', null],
            $field->format === 'integer' => ['int', null],
            $field->format === 'number' => ['float', null],
            $field->format === 'boolean' => ['bool', null],
            $field->format === 'object' => ['array', 'array<string, mixed>'],
            $field->format === 'array' => ['array', 'list<mixed>'],
            $field->format === 'unknown' => ['mixed', null],
            default => ['string', null],
        };
        if ($field->nullable && $native !== 'mixed') {
            $native = '?' . $native;
            $doc = $doc === null ? null : $doc . '|null';
        }

        return [$native, $doc];
    }

    /** The wire form of a pivot field read from `$expression`. */
    private static function pivotValue(PivotFieldDescriptor $field, string $expression): string
    {
        $access = $field->nullable ? '?->' : '->';

        return match (true) {
            $field->enum !== null => $expression . $access . 'value',
            $field->format === 'date-time' => $expression . $access . 'format(\\DateTimeInterface::ATOM)',
            $field->format === 'date' => $expression . $access . "format('Y-m-d')",
            default => $expression,
        };
    }

    private static function withMissing(string $type, string $missing): string
    {
        return \str_starts_with($type, '?') ? \substr($type, 1) . '|' . $missing . '|null' : (\str_ends_with($type, '|null') ? \substr($type, 0, -5) . '|' . $missing . '|null' : $type . '|' . $missing);
    }

    /**
     * The generated classes a relation's members hydrate into, sorted, skipping types the
     * document does not describe.
     *
     * @return list<string>
     */
    private static function relatedClasses(TypeSurface $type, RelationDescriptor $relation): array
    {
        $classes = [];
        foreach ($relation->types as $related) {
            $class = $type->classFor($related);
            if ($class !== null) {
                $classes[] = $class;
            }
        }
        $classes = \array_values(\array_unique($classes));
        \sort($classes, \SORT_STRING);

        return $classes;
    }

    /**
     * @param list<string> $classes
     */
    private static function union(array $classes): string
    {
        $classes = \array_values(\array_unique($classes));
        \sort($classes, \SORT_STRING);

        return \implode('|', $classes);
    }

    private static function collectionClass(Imports $imports, ?string $page): string
    {
        return $page === null ? $imports->runtime('Collections\\ResourceCollection') : $imports->runtime('Collections\\PaginatedCollection');
    }

    private static function collectionType(Imports $imports, string $member, ?string $page): string
    {
        if ($page === null) {
            return $imports->runtime('Collections\\ResourceCollection') . '<' . $member . '>';
        }

        return $imports->runtime('Collections\\PaginatedCollection') . '<' . $member . ', ' . $imports->runtime('Pagination\\' . $page) . '>';
    }

    private static function variable(RelationDescriptor $relation, string $fallback): string
    {
        $name = Naming::member($relation->name);

        return $name === '' || \in_array(\strtolower($name), self::RESERVED_VARIABLES, true) ? $fallback : $name;
    }

    /**
     * @param list<string> $items
     */
    private static function list(array $items): string
    {
        if (\count($items) < 2) {
            return \implode('', $items);
        }
        $last = \array_pop($items);

        return \implode(', ', $items) . ' and ' . $last;
    }
}
