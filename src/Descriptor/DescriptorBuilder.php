<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

use haddowg\JsonApiCodegen\Emit\Identifier;
use haddowg\JsonApiCodegen\Spec\Node;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;

/**
 * Builds the {@see ApiDescriptor} from an OpenAPI document.
 *
 * Everything is read structurally — a resource type is the `properties.type.const` its schema
 * declares, a collection path is the path whose create body or list response resolves to that
 * type — so nothing depends on a component being named a particular way beyond the `Resource`
 * suffix the enumeration starts from.
 */
final class DescriptorBuilder
{
    private const string JSON_API_MEDIA_TYPE = 'application/vnd.api+json';

    private const string ATOMIC_EXT = 'https://jsonapi.org/ext/atomic';

    private const string RESOURCE_SUFFIX = 'Resource';

    private const string IDENTIFIER_SUFFIX = 'ResourceIdentifier';

    private const string META_DOCUMENT = 'MetaDocument';

    private const string LOCATION_DOCUMENT = 'LocationDocument';

    /**
     * The roles each single strategy's `x-paginator.keys` may name. A key under any other role is
     * not one this codegen generates a parameter for.
     */
    private const array ROLES = [
        'page' => ['number', 'size'],
        'offset' => ['offset', 'limit'],
        'cursor' => ['after', 'before', 'size'],
        'fixed' => ['number'],
    ];

    /** The methods an action may be advertised under, in the order one is picked. */
    private const array ACTION_METHODS = ['post', 'get', 'put', 'patch', 'delete'];

    /** @var array<string, Node> resource schema name => schema */
    private array $resourceSchemas = [];

    /** @var array<string, string> resource schema name => JSON:API type */
    private array $wireTypes = [];

    /** @var array<string, string> JSON:API type => collection path */
    private array $collectionPaths = [];

    /** @var array<string, PaginatorDescriptor> JSON:API type => its collection's pagination */
    private array $typePaginators = [];

    /** @var array<string, string> operationId => the path it is advertised under */
    private array $operationPaths = [];

    /** @var list<string> what the document says that this codegen read around rather than generated */
    private array $warnings = [];

    /** @var list<string> the names of the document's `oauth2` security schemes, whose requirements list scopes */
    private array $scopeSchemes = [];

    private function __construct(private readonly SpecDocument $document) {}

    public static function build(SpecDocument $document): ApiDescriptor
    {
        return (new self($document))->descriptor();
    }

    private function descriptor(): ApiDescriptor
    {
        $this->collectResourceSchemas();
        $this->indexOperationIds();
        $this->scopeSchemes = $this->oauth2Schemes();
        $this->resolveCollectionPaths();

        $resources = [];
        foreach ($this->resourceSchemas as $name => $schema) {
            $type = $this->wireTypes[$name];
            $resources[$type] = $this->resource($type, $schema);
        }
        \ksort($resources, \SORT_STRING);

        return new ApiDescriptor(
            $resources,
            $this->enums(),
            $this->atomic(),
            $this->document->contract(),
            $this->errorCodes(),
            $this->warnings,
        );
    }

    /**
     * Every schema that describes a resource object: named `…Resource` (but not
     * `…ResourceIdentifier`) and carrying the `type` const that IS the JSON:API type. The const
     * is required once the name matches, so a malformed resource schema names its own pointer.
     */
    private function collectResourceSchemas(): void
    {
        foreach ($this->document->schemas() as $key => $schema) {
            $name = (string) $key;
            if (!\str_ends_with($name, self::RESOURCE_SUFFIX) || \str_ends_with($name, self::IDENTIFIER_SUFFIX)) {
                continue;
            }

            $this->resourceSchemas[$name] = $schema;
            $this->wireTypes[$name] = $schema->get('properties')->get('type')->get('const')->string();
        }
    }

    /**
     * The path each operationId is advertised under. A relation's endpoints are found by it,
     * because from contract 8 their segment is the relation's URI field name, which need not be
     * the relation's name.
     */
    private function indexOperationIds(): void
    {
        foreach ($this->document->paths() as $key => $item) {
            foreach ($item->members() as $operation) {
                $id = $operation->find('operationId');
                if ($id !== null && !isset($this->operationPaths[$id->string()])) {
                    $this->operationPaths[$id->string()] = (string) $key;
                }
            }
        }
    }

    /**
     * The collection path of each type: the one whose POST body is a create document for that
     * type, else the one whose GET returns a collection of it. Paths carrying `{id}` or an
     * action segment are excluded, so a parent-scoped related collection is never mistaken for
     * the type's own.
     */
    private function resolveCollectionPaths(): void
    {
        $byCreate = [];
        $byList = [];

        foreach ($this->document->paths() as $key => $item) {
            $path = (string) $key;
            if (\str_contains($path, '{id}') || \str_contains($path, '/-actions/')) {
                continue;
            }

            $post = $item->find('post');
            if ($post !== null) {
                $created = $this->documentPrimary($this->requestBodySchema($post));
                if ($created !== null && !isset($byCreate[$created['type']])) {
                    $byCreate[$created['type']] = $path;
                }
            }

            $get = $item->find('get');
            if ($get !== null) {
                $listed = $this->documentPrimary($this->okResponseSchema($get));
                if ($listed !== null && $listed['cardinality'] === Cardinality::Many && !isset($byList[$listed['type']])) {
                    $byList[$listed['type']] = $path;
                }
            }
        }

        foreach ($this->wireTypes as $type) {
            $path = $byCreate[$type] ?? $byList[$type] ?? null;
            if ($path !== null) {
                $this->collectionPaths[$type] = $path;
            }
            $this->typePaginators[$type] = $path === null
                ? PaginatorDescriptor::none()
                : $this->paginator($this->document->pathItem($path)?->find('get'));
        }
    }

    private function resource(string $type, Node $schema): ResourceDescriptor
    {
        $collection = $this->collectionPaths[$type] ?? null;
        $list = $collection === null ? null : $this->document->pathItem($collection)?->find('get');

        $single = $collection === null ? null : $this->document->pathItem($collection . '/{id}');
        $create = $collection === null ? null : $this->document->pathItem($collection)?->find('post');
        $update = $single?->find('patch');
        $writeOnly = $this->markedWriteOnly($create, $update);
        $relations = $this->relations($type, $schema, $collection, $writeOnly, $create, $update);
        $actions = $collection === null ? [] : $this->actions($collection);

        return new ResourceDescriptor(
            $type,
            $this->attributes($schema),
            $this->writeAttributes($create),
            $this->writeAttributes($update),
            $relations,
            $collection === null ? [] : $this->operations($collection),
            $this->typePaginators[$type] ?? PaginatorDescriptor::none(),
            $this->clientIdPolicy($collection),
            $this->countable($list),
            \array_values(\array_diff($this->tokenEnum($list, 'include'), $writeOnly)),
            $this->tokenEnum($list, 'sort'),
            $this->filterable($list),
            $actions,
            $this->writeRelations($create),
            $this->writeRelations($update),
            \array_values(\array_diff($this->memberOrder($schema), $writeOnly)),
            $this->writeOnlyRelations($type, $relations, $collection, $create, $update),
            $this->declaredLinks($schema, \array_values(\array_unique(\array_map(static fn(ActionDescriptor $action): string => $action->name, \array_values($actions))))),
        );
    }

    /**
     * The links a type declares, read from its `<Type>ResourceLinks`. Contract 14
     * lists each as a named property beside `self`: a link object as an inline object schema with
     * an `href`, a bare link as a `$ref` to `LinkObject`. `self` is never declared, an action's
     * link is the action's, and a `RenderedLink` is a link the server builds, so none of those is
     * read. An older document declares no links. Contract 18 moves the named links into the
     * second entry of an `allOf` beside the shared `ResourceLinks`, see {@see namedLinks()}.
     *
     * @param list<string> $actions the type's action names, whose links a server that renders
     *                              strings points at `LinkObject` just as it does a bare link
     *
     * @return array<string, DeclaredLinkDescriptor>
     */
    private function declaredLinks(Node $schema, array $actions): array
    {
        $links = $schema->path('properties', 'links');
        if ($links === null || ($this->document->contract() ?? 0) < 14) {
            return [];
        }

        $out = [];
        foreach ($this->namedLinks($links) as $key => $link) {
            $name = (string) $key;
            if ($name === 'self' || \in_array($name, $actions, true)) {
                continue;
            }

            $reference = $this->document->refName($link);
            if ($reference === 'RenderedLink') {
                continue;
            }
            $description = $link->find('description')?->string();
            if ($reference === 'LinkObject') {
                $out[$name] = new DeclaredLinkDescriptor($name, false, $description);

                continue;
            }
            if ($reference !== null || $link->path('properties', 'href') === null) {
                throw SpecException::malformed($link->pointer(), 'expected a declared link to be a link object schema with an `href`, or a `$ref` to LinkObject');
            }

            $required = $link->find('required')?->strings() ?? [];
            $members = [];
            foreach ($link->get('properties')->members() as $memberKey => $member) {
                $memberName = (string) $memberKey;
                if ($memberName === 'href' || $memberName === 'meta') {
                    continue;
                }
                $members[$memberName] = new LinkMemberDescriptor(
                    $memberName,
                    $member->find('const')?->string(),
                    \in_array($memberName, $required, true),
                );
            }

            $out[$name] = new DeclaredLinkDescriptor(
                $name,
                true,
                $description,
                $members,
                $this->linkMeta($link->path('properties', 'meta')),
            );
        }

        return $out;
    }

    /**
     * The named members of a resource's `links` schema. From contract 18 a type without named
     * links points at the shared `ResourceLinks`, and one with them at a `<Type>ResourceLinks`
     * that is an `allOf` of `ResourceLinks` and an object holding them. Before
     * that, the type's own component listed them beside `self`.
     *
     * @return array<string|int, Node>
     */
    private function namedLinks(Node $links): array
    {
        if ($this->document->refName($links) === 'ResourceLinks') {
            return [];
        }

        $schema = $this->document->dereference($links);
        $allOf = $schema->find('allOf');
        if ($allOf === null) {
            return $schema->find('properties')?->members() ?? [];
        }

        $out = [];
        foreach ($allOf->items() as $entry) {
            if ($this->document->refName($entry) === 'ResourceLinks') {
                continue;
            }
            if (!$entry->isObject() || $entry->find('$ref') !== null) {
                throw SpecException::malformed($entry->pointer(), 'expected a resource\'s links to be an allOf of a `$ref` to ResourceLinks and an object of named links');
            }
            foreach ($entry->find('properties')?->members() ?? [] as $key => $link) {
                $out[$key] = $link;
            }
        }

        return $out;
    }

    /**
     * A declared link's `meta` members, each projected the way a read attribute is. `required`
     * lists those a request cannot hide.
     *
     * @return array<string, LinkMetaDescriptor>
     */
    private function linkMeta(?Node $meta): array
    {
        $meta = $meta === null ? null : $this->document->dereference($meta);
        $required = $meta?->find('required')?->strings() ?? [];

        $out = [];
        foreach ($meta?->find('properties')?->members() ?? [] as $key => $member) {
            $name = (string) $key;
            $hint = $this->formatHint($member);
            $description = $member->find('description')?->string();
            $out[$name] = new LinkMetaDescriptor(
                $name,
                $hint['format'],
                $hint['enum'],
                $hint['nullable'],
                $this->compositeSchema($member, $hint['format']),
                \in_array($name, $required, true),
                $this->attributeConstraints($member),
                $description === '' ? null : $description,
                $member->find('example')?->raw(),
            );
        }

        return $out;
    }

    /**
     * The relations a write body marks `writeOnly`. Contract 8 puts the keyword beside the `$ref`
     * of a write-only relation's `relationships` member, so such a relation is
     * kept off the read model even if a read schema were to show it.
     *
     * @return list<string>
     */
    private function markedWriteOnly(?Node ...$operations): array
    {
        $out = [];
        foreach ($operations as $operation) {
            foreach ($this->writeRelationships($operation)?->members() ?? [] as $key => $property) {
                if ($property->find('writeOnly')?->bool() === true) {
                    $out[] = (string) $key;
                }
            }
        }

        return \array_values(\array_unique($out));
    }

    /** The `properties` of a write body's `relationships` member, or null when it declares none. */
    private function writeRelationships(?Node $operation): ?Node
    {
        $relationships = $operation === null
            ? null
            : $this->requestBodySchema($operation)?->path('properties', 'data', 'properties', 'relationships');

        return $relationships === null ? null : $this->document->dereference($relationships)->find('properties');
    }

    /**
     * The relations a write accepts that the resource schema does not show: the ones a contract-8
     * write body marks `writeOnly`, and on an older document, any the read schema leaves out.
     * Contract 5 drops a write-only relation from every read schema, so its
     * cardinality and types are read from the write document's own relationship schema.
     *
     * @param array<string, RelationDescriptor> $readable
     *
     * @return array<string, RelationDescriptor>
     */
    private function writeOnlyRelations(string $type, array $readable, ?string $collection, ?Node ...$operations): array
    {
        $out = [];
        foreach ($operations as $operation) {
            foreach ($this->writeRelationships($operation)?->members() ?? [] as $key => $property) {
                $name = (string) $key;
                if (!isset($readable[$name]) && !isset($out[$name])) {
                    $out[$name] = $this->relation($type, $name, $this->document->dereference($property), $collection, $property);
                }
            }
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * The relations one write operation accepts, in document order. They are read from the
     * request document because they differ from the resource's relations and between create and
     * update: `tracks.playlists` is accepted on create and not on update, where the relation
     * permits no replacement.
     *
     * @return list<string>
     */
    private function writeRelations(?Node $operation): array
    {
        $relationships = $operation === null
            ? null
            : $this->requestBodySchema($operation)?->path('properties', 'data', 'properties', 'relationships');
        $properties = $relationships === null ? null : $this->document->dereference($relationships)->find('properties');

        return \array_map(static fn(int|string $key): string => (string) $key, \array_keys($properties?->members() ?? []));
    }

    /**
     * The resource schema's attributes then relations, in the order it declares them.
     *
     * @return list<string>
     */
    private function memberOrder(Node $schema): array
    {
        $names = [];
        $attributes = $schema->path('properties', 'attributes');
        $attributes = $attributes === null ? null : $this->document->dereference($attributes)->find('properties');
        foreach ([$attributes, $schema->path('properties', 'relationships', 'properties')] as $properties) {
            foreach (\array_keys($properties?->members() ?? []) as $key) {
                $names[] = (string) $key;
            }
        }

        return $names;
    }

    /**
     * The type's attributes, read from the resource schema's own `attributes` member (following
     * a `$ref` when it is one) rather than from a component guessed by name.
     *
     * @return array<string, AttributeDescriptor>
     */
    private function attributes(Node $schema): array
    {
        $properties = $schema->path('properties', 'attributes');
        if ($properties === null) {
            return [];
        }

        $properties = $this->document->dereference($properties)->find('properties');
        if ($properties === null) {
            return [];
        }

        $out = [];
        foreach ($properties->members() as $key => $attribute) {
            $name = (string) $key;
            $hint = $this->formatHint($attribute);
            $out[$name] = new AttributeDescriptor(
                $name,
                $hint['format'],
                $hint['enum'],
                $hint['nullable'],
                $this->compositeSchema($attribute, $hint['format'], $this->readsHoldConstraints()),
                $this->readsHoldConstraints() ? $this->attributeConstraints($attribute) : Constraints::none(),
            );
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * The attributes one write operation accepts, read from its request document rather than
     * from the read attributes with a writability flag laid over them.
     *
     * The create and update sets diverge in the document — an attribute can be settable on
     * create and not on update — so each is read from its own operation, and a read-only
     * attribute is simply absent from both.
     *
     * @return array<string, WriteAttributeDescriptor>
     */
    private function writeAttributes(?Node $operation): array
    {
        if ($operation === null) {
            return [];
        }

        $attributes = $this->requestBodySchema($operation)?->path('properties', 'data', 'properties', 'attributes');
        if ($attributes === null) {
            return [];
        }

        $attributes = $this->document->dereference($attributes);
        $properties = $attributes->find('properties');
        if ($properties === null) {
            return [];
        }

        $required = $attributes->find('required')?->strings() ?? [];

        $out = [];
        foreach ($properties->members() as $key => $attribute) {
            $name = (string) $key;
            $hint = $this->formatHint($attribute);
            $out[$name] = new WriteAttributeDescriptor(
                $name,
                $hint['format'],
                $hint['enum'],
                $hint['nullable'],
                $this->compositeSchema($attribute, $hint['format']),
                \in_array($name, $required, true),
                $this->attributeConstraints($attribute),
            );
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * The value's wire format, whether it admits null, and — when it is drawn from a named enum
     * component — that component's name.
     *
     * The format is an explicit `format` where one is declared, else the JSON type with `null`
     * dropped, else `string` for a bare enum. `unknown` only where the schema declares neither.
     * Nullability is tracked separately because it is what separates `?float` from `float`, and
     * dropping `null` to find the format would otherwise lose it.
     *
     * @return array{format: string, enum: string|null, nullable: bool}
     */
    private function formatHint(Node $schema): array
    {
        $unwrapped = $this->unwrapNullable($schema);
        $reference = $this->document->refName($unwrapped);
        $target = $reference === null ? $unwrapped : $this->document->schema($reference);
        $isEnum = $target->find('enum') !== null;

        $format = $target->find('format')?->string()
            ?? $this->jsonType($target)
            ?? ($isEnum ? 'string' : 'unknown');

        if ($isEnum && $reference !== null) {
            self::assertClassName($reference);
        }

        return [
            'format' => $format,
            'enum' => $isEnum ? $reference : null,
            'nullable' => $this->admitsNull($schema) || $this->admitsNull($target),
        ];
    }

    /**
     * The whole value schema, for the values a format hint cannot describe on its own: an
     * object's members, an array's item type, and any composed schema — a `oneOf` union has no
     * format at all, and its branches are the only description of it there is.
     */
    private function compositeSchema(Node $schema, string $format, bool $constrained = true): ?ValueSchema
    {
        $target = $this->document->dereference($this->unwrapNullable($schema));
        $composed = $target->find('oneOf') !== null
            || $target->find('anyOf') !== null
            || $target->find('allOf') !== null;

        return $composed || \in_array($format, ['object', 'array'], true)
            ? $this->valueSchema($schema, $constrained)
            : null;
    }

    /**
     * Whether a response schema's constraints hold for every value it describes. Before contract
     * 5 the read schema took the update context's keywords, which a create could have broken, so
     * an older document's read constraints are not read at all. A write schema's always held for
     * its own write.
     */
    private function readsHoldConstraints(): bool
    {
        return ($this->document->contract() ?? 0) >= 5;
    }

    /**
     * The one branch of a nullable union, `anyOf`/`oneOf` of a schema and `{"type": "null"}`,
     * which is how contract 5 makes a `$ref`'d enum nullable. Any other schema comes back as it
     * is: nullability is read from the union itself by {@see admitsNull()}.
     */
    private function unwrapNullable(Node $schema): Node
    {
        if ($schema->find('type') !== null || $this->document->reference($schema) !== null) {
            return $schema;
        }

        foreach (['anyOf', 'oneOf'] as $keyword) {
            $branches = $schema->find($keyword)?->items();
            if ($branches === null || $schema->find($keyword === 'anyOf' ? 'oneOf' : 'anyOf') !== null || $schema->find('allOf') !== null) {
                continue;
            }
            $kept = \array_values(\array_filter($branches, fn(Node $branch): bool => $this->declaredTypes($branch) !== ['null']));
            if (\count($kept) === 1 && \count($branches) === 2) {
                return $kept[0];
            }
        }

        return $schema;
    }

    /**
     * An attribute's constraints: the keywords on its own schema, then those on a nullable
     * union's branch, then those on the schema a `$ref` stands for. The nearer keyword wins, so
     * a `default` beside a `$ref` (or beside the `allOf` that wraps one) outranks the target's.
     */
    private function attributeConstraints(Node $schema): Constraints
    {
        $own = $this->constraints($schema);
        $branch = $this->unwrapNullable($schema);
        if ($branch !== $schema) {
            $own = $own->orElse($this->constraints($branch));
        }
        $target = $this->document->dereference($branch);

        return $target === $branch ? $own : $own->orElse($this->constraints($target));
    }

    /**
     * The validation keywords one schema node declares. A keyword of the wrong JSON type is a
     * malformed document, reported at its pointer.
     */
    private function constraints(Node $schema): Constraints
    {
        $number = static fn(string $keyword): int|float|null => $schema->find($keyword)?->number();
        $count = static fn(string $keyword): ?int => $schema->find($keyword)?->int();
        $default = $schema->find('default')?->raw();

        return new Constraints(
            $number('minimum'),
            $number('maximum'),
            $number('exclusiveMinimum'),
            $number('exclusiveMaximum'),
            $number('multipleOf'),
            $count('minLength'),
            $count('maxLength'),
            $schema->find('pattern')?->string(),
            $count('minItems'),
            $count('maxItems'),
            $schema->find('uniqueItems')?->bool() ?? false,
            $count('minProperties'),
            $count('maxProperties'),
            \is_scalar($default) ? $default : null,
        );
    }

    /**
     * The JSON types a schema declares, `null` among them when it is nullable.
     *
     * @return list<string>
     */
    private function declaredTypes(Node $schema): array
    {
        $type = $schema->find('type');
        if ($type === null) {
            return [];
        }

        return $type->isString() ? [$type->string()] : $type->strings();
    }

    /**
     * Whether a schema admits null — declared among its own types, or by a composition branch
     * that is `{"type": "null"}`, which is how a nullable union spells it.
     */
    private function admitsNull(Node $schema): bool
    {
        if (\in_array('null', $this->declaredTypes($schema), true)) {
            return true;
        }

        foreach (['oneOf', 'anyOf'] as $keyword) {
            foreach ($schema->find($keyword)?->items() ?? [] as $branch) {
                if (\in_array('null', $this->declaredTypes($branch), true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** The first declared JSON type with `null` dropped, or null when the schema declares none. */
    private function jsonType(Node $schema): ?string
    {
        foreach ($this->declaredTypes($schema) as $candidate) {
            if ($candidate !== 'null') {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param list<string> $writeOnly relations a write body marks `writeOnly`, which no read shows
     *
     * @return array<string, RelationDescriptor>
     */
    private function relations(string $type, Node $schema, ?string $collection, array $writeOnly, ?Node $create, ?Node $update): array
    {
        $properties = $schema->path('properties', 'relationships', 'properties');
        if ($properties === null) {
            return [];
        }

        $out = [];
        foreach ($properties->members() as $key => $property) {
            $name = (string) $key;
            if (\in_array($name, $writeOnly, true)) {
                continue;
            }
            $written = $this->writeRelationships($update)?->find($name) ?? $this->writeRelationships($create)?->find($name);
            $out[$name] = $this->relation($type, $name, $this->document->dereference($property), $collection, $written);
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * @param Node|null $written the relation's member in a create or update body, which carries the
     *                           pivot a write sends when no relationship endpoint does
     */
    private function relation(string $type, string $name, Node $component, ?string $collection, ?Node $written): RelationDescriptor
    {
        $data = $component->get('properties')->get('data');
        $cardinality = $data->find('type')?->string() === 'array' ? Cardinality::Many : Cardinality::One;
        $linkage = $this->linkage($cardinality === Cardinality::Many ? $data->get('items') : $data, $this->readsHoldConstraints());

        $relatedPath = $collection === null ? null : $this->relationPath($type, $name, $collection . '/{id}/' . $name, 'fetchRelated');
        $relationshipPath = $collection === null ? null : $this->relationPath(
            $type,
            $name,
            $collection . '/{id}/relationships/' . $name,
            'fetchRelationship',
            'updateRelationship',
            'addRelationship',
            'removeRelationship',
        );
        $related = $relatedPath === null ? null : $this->document->pathItem($relatedPath);
        $relationship = $relationshipPath === null ? null : $this->document->pathItem($relationshipPath);
        $relatedGet = $related?->find('get');
        $relationshipGet = $relationship?->find('get');
        $polymorphic = $linkage['polymorphic'] || \count($linkage['types']) > 1;

        return new RelationDescriptor(
            $name,
            $cardinality,
            $linkage['types'],
            $linkage['pivot'],
            $linkage['pivotFields'],
            $this->operationAt($related, 'get', (string) $relatedPath),
            $this->operationAt($relationship, 'get', (string) $relationshipPath),
            $this->relationMutations($relationship, $cardinality, (string) $relationshipPath),
            $this->countable($relatedGet) ?? $this->countable($relationshipGet),
            $this->relationPaginator($cardinality, $linkage['types'], $polymorphic, $relatedGet, $relationshipGet),
            $linkage['pivot'] ? $this->writePivotFields($relationship, $written, $linkage['pivotFields']) : [],
            $polymorphic,
        );
    }

    /**
     * The path of one of a relation's endpoints, found by its operationId (`fetchRelated.<type>.<rel>`
     * and so on), because from contract 8 the segment is the relation's URI field name. A document
     * without operationIds is read by the relation's name instead, as long as the path there does
     * not belong to a different relation. Null when the relation has no such endpoint.
     */
    private function relationPath(string $type, string $name, string $byName, string ...$operations): ?string
    {
        $expected = \array_map(static fn(string $operation): string => $operation . '.' . $type . '.' . $name, $operations);
        foreach ($expected as $id) {
            if (isset($this->operationPaths[$id])) {
                return $this->operationPaths[$id];
            }
        }

        $item = $this->document->pathItem($byName);
        foreach ($item?->members() ?? [] as $operation) {
            $id = $operation->find('operationId');
            if ($id !== null && !\in_array($id->string(), $expected, true)) {
                return null;
            }
        }

        return $byName;
    }

    /**
     * The pivot fields a write sends, read from the request body of the relationship endpoint, or
     * of a create or update when the relation has no mutation endpoint. From contract 8 that body
     * names its own `<Type><Rel>WritePivot`, which leaves out what no write can set. An older
     * document shares one pivot schema between both directions and flags those `readOnly`, so
     * they are dropped here either way.
     *
     * @param array<string, PivotFieldDescriptor> $read
     *
     * @return array<string, PivotFieldDescriptor>
     */
    private function writePivotFields(?Node $relationship, ?Node $written, array $read): array
    {
        $data = null;
        foreach (['patch', 'post'] as $method) {
            $operation = $relationship?->find($method);
            $data = $operation === null ? null : $this->requestBodySchema($operation)?->path('properties', 'data');
            if ($data !== null) {
                break;
            }
        }
        $data ??= $written === null ? null : $this->document->dereference($written)->path('properties', 'data');

        $fields = $read;
        if ($data !== null) {
            $fields = $this->linkage($data->find('type')?->string() === 'array' ? $data->get('items') : $data, true)['pivotFields'];
        }

        return \array_filter($fields, static fn(PivotFieldDescriptor $field): bool => !$field->readOnly);
    }

    /**
     * The related types a linkage names, its pivot shape, and whether it is a union.
     *
     * Four shapes reach here: a `$ref` to an identifier, an `allOf` pairing one with a
     * `meta.pivot` block, an `anyOf` (a nullable to-one, or a polymorphic set), and an `anyOf`
     * nested one level deeper for a polymorphic to-one. Anything else is a linkage grammar this
     * codegen has not been taught, and errors rather than being dropped.
     *
     * An `anyOf` with no `null` branch is a polymorphic set, whatever its member count: from
     * contract 16 a `MorphTo` or `MorphToMany` over one type is a one-member union (core ADR
     * 0184). The `anyOf` that only makes a to-one nullable is not, so a nullable `BelongsTo` is
     * not mistaken for one.
     *
     * @return array{types: list<string>, pivot: bool, pivotFields: array<string, PivotFieldDescriptor>, polymorphic: bool}
     */
    private function linkage(Node $node, bool $constrained): array
    {
        if ($this->document->refName($node) !== null) {
            return ['types' => [$this->identifierType($node)], 'pivot' => false, 'pivotFields' => [], 'polymorphic' => false];
        }

        $allOf = $node->find('allOf');
        if ($allOf !== null) {
            $types = [];
            $fields = [];
            foreach ($allOf->items() as $entry) {
                if ($this->document->refName($entry) !== null) {
                    $types[] = $this->identifierType($entry);

                    continue;
                }
                $fields = [...$fields, ...$this->pivotFields($entry, $constrained)];
            }
            if ($types === []) {
                throw SpecException::malformed($allOf->pointer(), 'to reference a resource identifier');
            }
            \ksort($fields, \SORT_STRING);

            return ['types' => $types, 'pivot' => true, 'pivotFields' => $fields, 'polymorphic' => false];
        }

        $anyOf = $node->find('anyOf');
        if ($anyOf !== null) {
            $types = [];
            $pivot = false;
            $fields = [];
            $nullable = false;
            $polymorphic = false;
            foreach ($anyOf->items() as $entry) {
                if ($entry->find('type')?->string() === 'null') {
                    $nullable = true;

                    continue;
                }
                $nested = $this->linkage($entry, $constrained);
                $types = [...$types, ...$nested['types']];
                $pivot = $pivot || $nested['pivot'];
                $fields = [...$fields, ...$nested['pivotFields']];
                $polymorphic = $polymorphic || $nested['polymorphic'];
            }
            if ($types === []) {
                throw SpecException::malformed($anyOf->pointer(), 'to reference at least one resource identifier');
            }

            return ['types' => $types, 'pivot' => $pivot, 'pivotFields' => $fields, 'polymorphic' => $polymorphic || !$nullable || \count($types) > 1];
        }

        throw SpecException::malformed(
            $node->pointer(),
            'to declare linkage as a $ref, an allOf carrying one, or an anyOf of them',
        );
    }

    /** The JSON:API type a resource identifier `$ref` resolves to. */
    private function identifierType(Node $reference): string
    {
        $target = $this->document->follow($reference)
            ?? throw SpecException::malformed($reference->pointer(), 'to reference a resource identifier');

        return $target->get('properties')->get('type')->get('const')->string();
    }

    /**
     * The `meta.pivot` field shape carried by one `allOf` entry, with each field's `readOnly`
     * flag and its membership of the pivot object's `required` list. From contract 8 the pivot is
     * a `$ref` to `<Type><Rel>Pivot` on a read and `<Type><Rel>WritePivot` on a write (core ADR
     * 0160), where a read's `required` lists every field the server always renders.
     *
     * @param bool $constrained whether the schema's constraints hold for every value it describes
     *
     * @return array<string, PivotFieldDescriptor>
     */
    private function pivotFields(Node $entry, bool $constrained): array
    {
        $pivot = $entry->path('properties', 'meta', 'properties', 'pivot');
        $pivot = $pivot === null ? null : $this->document->dereference($pivot);
        $properties = $pivot?->find('properties');
        if ($pivot === null || $properties === null) {
            return [];
        }

        $required = $pivot->find('required')?->strings() ?? [];

        $out = [];
        foreach ($properties->members() as $key => $field) {
            $name = (string) $key;
            $hint = $this->formatHint($field);
            $out[$name] = new PivotFieldDescriptor(
                $name,
                $hint['format'],
                $hint['enum'],
                $hint['nullable'],
                $this->compositeSchema($field, $hint['format'], $constrained),
                $field->find('readOnly')?->bool() === true,
                \in_array($name, $required, true),
                $constrained ? $this->attributeConstraints($field) : Constraints::none(),
            );
        }

        return $out;
    }

    /**
     * The mutations one relationship endpoint advertises, derived from the methods it exposes.
     * A to-many maps POST to `add`, DELETE to `remove` and PATCH to `replace`; a to-one maps
     * PATCH to `set`. Each carries its own error statuses, which differ from the read's.
     *
     * @return array<string, OperationDescriptor>
     */
    private function relationMutations(?Node $pathItem, Cardinality $cardinality, string $path): array
    {
        if ($pathItem === null) {
            return [];
        }

        $candidates = $cardinality === Cardinality::One
            ? [['patch', RelationVerb::Set]]
            : [['post', RelationVerb::Add], ['delete', RelationVerb::Remove], ['patch', RelationVerb::Replace]];

        $out = [];
        foreach ($candidates as [$method, $verb]) {
            $operation = $this->operationAt($pathItem, $method, $path);
            if ($operation !== null) {
                $out[$verb->value] = $operation;
            }
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * A relation's own pagination, carried only when its related endpoint paginates differently
     * from the related type's collection. Null means the two agree, and the related type's
     * applies. The member keys are compared too, not just the kind — a strategy that matches but
     * renames its page key is still a different thing to put on the wire.
     *
     * A polymorphic to-many has no one related type to agree with, so it always carries its own,
     * which is none when its endpoints advertise no `page`.
     *
     * @param list<string> $types
     */
    private function relationPaginator(Cardinality $cardinality, array $types, bool $polymorphic, ?Node $relatedGet, ?Node $relationshipGet): ?PaginatorDescriptor
    {
        if ($cardinality === Cardinality::One) {
            return null;
        }

        $paginator = $this->paginator($relatedGet);
        if (!$paginator->paginated()) {
            $paginator = $this->paginator($relationshipGet);
        }
        if ($polymorphic) {
            return $paginator;
        }

        $relatedType = $types[0] ?? null;
        $typePaginator = $relatedType === null
            ? PaginatorDescriptor::none()
            : ($this->typePaginators[$relatedType] ?? PaginatorDescriptor::none());

        return $paginator->paginated() && !$paginator->matches($typePaginator) ? $paginator : null;
    }

    /**
     * The pagination an operation advertises.
     *
     * A page schema stamped with `x-paginator` (contract 3) names its strategy and the key that
     * fills each role, and that stamp is the whole answer: names are never consulted beside it.
     * Without one, the strategy is inferred from the member names, and only from the canonical
     * sets (see {@see inferred()}), because a renamed key is exactly what the stamp exists to
     * describe.
     *
     * Two wire forms carry unstamped members and both are read. The current projector emits a
     * single `page` object parameter (`style: deepObject`) whose schema declares them; older
     * documents flatten them into one parameter each (`page[number]`).
     *
     * Nothing here throws for a shape it does not recognise. An unknown strategy, an unknown
     * member set or an unstamped menu is a warning, and the endpoint generates unpaginated: a
     * client that cannot page is still a working client, where a codegen that refuses the whole
     * document is not.
     */
    private function paginator(?Node $operation): PaginatorDescriptor
    {
        if ($operation === null) {
            return PaginatorDescriptor::none();
        }

        $label = self::label($operation);
        $members = [];
        $bounds = [];

        foreach ($operation->find('parameters')?->items() ?? [] as $parameter) {
            $name = $parameter->find('name')?->string();
            if ($name === null) {
                continue;
            }

            if ($name === 'page') {
                $schema = $parameter->find('schema');
                if ($schema === null) {
                    continue;
                }
                if ($schema->find('x-paginator') !== null) {
                    return $this->stamped($schema, $label) ?? PaginatorDescriptor::none();
                }
                if ($schema->find('oneOf') !== null) {
                    $this->warn(\sprintf('%s offers a menu of pagination strategies with no `x-paginator` naming its default, so its reads are generated unpaginated. A server on generator contract 3 or later stamps it.', $label));

                    return PaginatorDescriptor::none();
                }
                foreach (\array_keys($schema->find('properties')?->members() ?? []) as $member) {
                    $members[] = (string) $member;
                }
                $bounds = $this->memberConstraints($schema);

                continue;
            }

            if (\str_starts_with($name, 'page[') && \str_ends_with($name, ']')) {
                $member = \substr($name, \strlen('page['), -1);
                $members[] = $member;
                $schema = $parameter->find('schema');
                if ($schema !== null) {
                    $bounds[$member] = $this->constraints($schema);
                }
            }
        }

        if ($members === []) {
            return PaginatorDescriptor::none();
        }

        return $this->inferred($members, $label)?->bounded($bounds) ?? PaginatorDescriptor::none();
    }

    /**
     * A strategy read from the `x-paginator` stamp on a page schema, or null (having warned)
     * when it is one this codegen cannot generate.
     */
    private function stamped(Node $schema, string $label, ?string $arm = null): ?PaginatorDescriptor
    {
        $stamp = $schema->get('x-paginator');
        $strategy = $stamp->get('strategy')->string();
        $where = $arm === null ? $label : \sprintf('the `%s` strategy of %s', $arm, $label);

        if ($strategy === PaginatorKind::Multi->value && $arm === null) {
            return $this->menu($schema, $stamp->get('default')->string(), $label);
        }

        $kind = PaginatorKind::tryFrom($strategy);
        $roles = $kind === null ? null : self::ROLES[$kind->value] ?? null;
        if ($kind === null || $roles === null) {
            $this->warn(\sprintf('%s paginates by the `%s` strategy, which this codegen does not know, so %s generated unpaginated.', $where, $strategy, $arm === null ? 'its reads are' : 'it cannot be picked and is'));

            return null;
        }

        $keys = [];
        foreach ($stamp->find('keys')?->members() ?? [] as $role => $key) {
            if (\in_array((string) $role, $roles, true)) {
                $keys[(string) $role] = $key->string();
            }
        }

        return $this->strategy($kind, $keys, $where)?->bounded($this->memberConstraints($schema));
    }

    /**
     * A `MultiPaginator` menu: one arm per `oneOf` member, each selected by the const its
     * discriminator member declares.
     *
     * An arm this codegen cannot read is dropped with a warning, and the rest stay pickable. The
     * default arm cannot be dropped that way: an unchosen read is typed as its page, so without
     * it the whole collection generates unpaginated.
     */
    private function menu(Node $schema, string $default, string $label): ?PaginatorDescriptor
    {
        $discriminator = $schema->path('discriminator', 'propertyName')?->string() ?? 'kind';
        $arms = [];

        foreach ($schema->get('oneOf')->items() as $branch) {
            $kind = $branch->get('properties')->get($discriminator)->get('const')->string();
            if ($branch->find('x-paginator') !== null) {
                $arm = $this->stamped($branch, $label, $kind);
            } else {
                $members = \array_values(\array_filter(
                    \array_map('strval', \array_keys($branch->find('properties')?->members() ?? [])),
                    static fn(string $member): bool => $member !== $discriminator,
                ));
                $arm = $this->inferred($members, \sprintf('the `%s` strategy of %s', $kind, $label))?->bounded($this->memberConstraints($branch));
            }

            if ($arm !== null) {
                $arms[$kind] = $arm;
            }
        }

        if (!isset($arms[$default])) {
            $this->warn(\sprintf('%s defaults to the `%s` strategy, which is not one this codegen can read, so its reads are generated unpaginated.', $label, $default));

            return null;
        }

        return PaginatorDescriptor::menu($arms, $default, $discriminator);
    }

    /**
     * A strategy inferred from unstamped member names, or null (having warned) when they are not
     * one of the canonical sets: `number` and `size`, `offset` and `limit`, a cursor bound with
     * an optional `size`, or a lone `number`.
     *
     * Only the canonical names are recognised. A renamed key cannot be told apart from a custom
     * paginator's own, and guessing wrong puts the wrong parameter on the wire, so a server that
     * renames keys has to say so with `x-paginator`.
     *
     * @param list<string> $members
     */
    private function inferred(array $members, string $where): ?PaginatorDescriptor
    {
        $members = \array_values(\array_unique($members));
        \sort($members, \SORT_STRING);

        $cursor = \array_diff($members, ['after', 'before', 'size']) === [] && \array_intersect($members, ['after', 'before']) !== [];
        $kind = match (true) {
            $members === ['number', 'size'] => PaginatorKind::Page,
            $members === ['limit', 'offset'] => PaginatorKind::Offset,
            $members === ['number'] => PaginatorKind::Fixed,
            $cursor => PaginatorKind::Cursor,
            default => null,
        };

        if ($kind === null) {
            $this->warn(\sprintf(
                '%s pages by %s, which match no paginator this codegen recognises by name, and carries no `x-paginator`; %s generated unpaginated.',
                $where,
                \implode(', ', \array_map(static fn(string $member): string => '`' . $member . '`', $members)),
                \str_starts_with($where, 'the `') ? 'it cannot be picked and is' : 'its reads are',
            ));

            return null;
        }

        return new PaginatorDescriptor($kind, \array_combine($members, $members));
    }

    /**
     * A single strategy from its role-to-key map, or null (having warned) when a role it cannot
     * page without is missing.
     *
     * @param array<string, string> $keys
     */
    private function strategy(PaginatorKind $kind, array $keys, string $where): ?PaginatorDescriptor
    {
        $required = match ($kind) {
            PaginatorKind::Page, PaginatorKind::Fixed => isset($keys['number']),
            PaginatorKind::Offset => isset($keys['offset']),
            PaginatorKind::Cursor => isset($keys['after']) || isset($keys['before']),
            default => false,
        };

        if (!$required) {
            $this->warn(\sprintf('%s declares the `%s` strategy without the key it pages by, so %s generated unpaginated.', $where, $kind->value, \str_starts_with($where, 'the `') ? 'it cannot be picked and is' : 'its reads are'));

            return null;
        }

        \ksort($keys, \SORT_STRING);

        return new PaginatorDescriptor($kind, $keys);
    }

    /**
     * The constraints of each member a page schema declares, keyed by member name.
     *
     * @return array<string, Constraints>
     */
    private function memberConstraints(Node $schema): array
    {
        $out = [];
        foreach ($schema->find('properties')?->members() ?? [] as $member => $property) {
            $out[(string) $member] = $this->constraints($property);
        }

        return $out;
    }

    /** A component whose name the generated code declares as a class or enum. */
    private static function assertClassName(string $component): void
    {
        if (!Identifier::isClassName($component)) {
            throw SpecException::unusableName('components.schemas.' . $component, $component, 'class name');
        }
    }

    private function warn(string $warning): void
    {
        if (!\in_array($warning, $this->warnings, true)) {
            $this->warnings[] = $warning;
        }
    }

    /** `GET /albums`, from the operation's pointer (`paths./albums.get`). */
    private static function label(Node $operation): string
    {
        $pointer = $operation->pointer();
        if (\preg_match('/^paths\.(.+)\.([a-z]+)$/', $pointer, $matches) === 1) {
            return '`' . \strtoupper($matches[2]) . ' ' . $matches[1] . '`';
        }

        return '`' . $pointer . '`';
    }

    /**
     * The `withCount` capability an operation advertises: the tokens from its parameter's enum
     * and the negotiation profile from `x-profile`. A `withCount` with no `x-profile` is one the
     * client cannot tell the server it is using, so it counts as absent.
     */
    private function countable(?Node $operation): ?CountableDescriptor
    {
        if ($operation === null) {
            return null;
        }

        foreach ($operation->find('parameters')?->items() ?? [] as $parameter) {
            if ($parameter->find('name')?->string() !== 'withCount') {
                continue;
            }
            $profile = $parameter->find('x-profile')?->string();
            if ($profile === null) {
                return null;
            }

            return new CountableDescriptor($parameter->path('schema', 'items', 'enum')?->strings() ?? [], $profile);
        }

        return null;
    }

    /**
     * The string tokens of an array-valued query parameter's enum — the shape `include` and
     * `sort` both use.
     *
     * @return list<string>
     */
    private function tokenEnum(?Node $operation, string $parameterName): array
    {
        if ($operation === null) {
            return [];
        }

        foreach ($operation->find('parameters')?->items() ?? [] as $parameter) {
            if ($parameter->find('name')?->string() === $parameterName) {
                return $parameter->path('schema', 'items', 'enum')?->strings() ?? [];
            }
        }

        return [];
    }

    /**
     * The `include` paths and `fields[TYPE]` members an operation declares, or null when it
     * declares neither and so accepts no projection.
     */
    private function projection(Node $operation): ?ProjectionDescriptor
    {
        $includable = null;
        $fieldsets = [];

        foreach ($operation->find('parameters')?->items() ?? [] as $parameter) {
            $name = $parameter->find('name')?->string();
            if ($name === 'include') {
                $includable = $parameter->path('schema', 'items', 'enum')?->strings() ?? [];
            } elseif ($name !== null && \str_starts_with($name, 'fields[') && \str_ends_with($name, ']')) {
                $fieldsets[\substr($name, \strlen('fields['), -1)] = $parameter->path('schema', 'items', 'enum')?->strings() ?? [];
            }
        }

        if ($includable === null && $fieldsets === []) {
            return null;
        }
        \ksort($fieldsets, \SORT_STRING);

        return new ProjectionDescriptor($includable ?? [], $fieldsets);
    }

    /**
     * The error catalogue: every `<Name>Error` schema, each narrowing `code` and `status` to a
     * constant over the open `Error`. A variant that narrows neither is a malformed catalogue and
     * fails on its own pointer rather than being skipped.
     *
     * @return array<string, ErrorCodeDescriptor> keyed and sorted by code
     */
    private function errorCodes(): array
    {
        $out = [];

        foreach ($this->document->schemas() as $name => $schema) {
            $name = (string) $name;
            if ($name === 'Error' || !\str_ends_with($name, 'Error')) {
                continue;
            }

            $narrowing = null;
            foreach ($schema->find('allOf')?->items() ?? [] as $part) {
                if ($part->find('properties') !== null) {
                    $narrowing = $part->get('properties');
                }
            }
            $narrowing ??= $schema->get('properties');

            self::assertClassName($name);
            $code = $narrowing->get('code')->get('const')->string();
            $required = $narrowing->path('source', 'required')?->strings() ?? [];
            $description = $schema->find('description')?->string();
            $example = $narrowing->path('detail', 'example')?->string();

            $out[$code] = new ErrorCodeDescriptor(
                $code,
                (int) $narrowing->get('status')->get('const')->string(),
                $name,
                $schema->find('title')?->string() ?? $code,
                match (true) {
                    \in_array('pointer', $required, true) => ErrorSourceKind::Pointer,
                    \in_array('parameter', $required, true) => ErrorSourceKind::Parameter,
                    default => null,
                },
                $description === '' ? null : $description,
                $example === '' ? null : $example,
            );
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * The `filter[…]` keys a collection read accepts, in the order the document declares them.
     *
     * Document order rather than sorted, like `include` and `sort`: it is the order the server
     * declared its filters in, so generated filter methods and shapes read the way the API does.
     *
     * @return array<string, FilterDescriptor>
     */
    private function filterable(?Node $operation): array
    {
        if ($operation === null) {
            return [];
        }

        $out = [];
        foreach ($operation->find('parameters')?->items() ?? [] as $parameter) {
            $name = $parameter->find('name')?->string();
            if ($name === null || !\str_starts_with($name, 'filter[') || !\str_ends_with($name, ']')) {
                continue;
            }
            $key = \substr($name, \strlen('filter['), -1);
            $schema = $parameter->find('schema');
            $out[$key] = new FilterDescriptor(
                $key,
                $schema === null || $schema->members() === [] ? null : $this->valueSchema($schema),
            );
        }

        return $out;
    }

    private function valueSchema(Node $schema, bool $constrained = true): ValueSchema
    {
        $reference = $schema;
        $schema = $this->document->dereference($schema);

        $properties = [];
        foreach ($schema->find('properties')?->members() ?? [] as $key => $property) {
            $properties[(string) $key] = $this->valueSchema($property, $constrained);
        }

        $required = $schema->find('required')?->strings() ?? [];
        \sort($required, \SORT_STRING);

        $items = $schema->find('items');
        $const = $schema->find('const');

        return new ValueSchema(
            $this->declaredTypes($schema),
            $schema->find('format')?->string(),
            $properties,
            $items === null ? null : $this->valueSchema($items, $constrained),
            $required,
            \array_values(\array_filter($schema->find('enum')?->enumValues() ?? [], \is_string(...))),
            $this->branches($schema, 'oneOf', $constrained),
            $this->branches($schema, 'anyOf', $constrained),
            $this->branches($schema, 'allOf', $constrained),
            $schema->path('discriminator', 'propertyName')?->string(),
            $const !== null && $const->isString() ? $const->string() : null,
            match (true) {
                !$constrained => Constraints::none(),
                $reference === $schema => $this->constraints($schema),
                default => $this->constraints($reference)->orElse($this->constraints($schema)),
            },
        );
    }

    /**
     * One composition keyword's branches, in document order.
     *
     * @return list<ValueSchema>
     */
    private function branches(Node $schema, string $keyword, bool $constrained): array
    {
        $out = [];
        foreach ($schema->find($keyword)?->items() ?? [] as $branch) {
            $out[] = $this->valueSchema($branch, $constrained);
        }

        return $out;
    }

    /**
     * Whether a create accepts a client-generated id. `id: false` on the create document is the
     * projector's way of forbidding one outright.
     */
    private function clientIdPolicy(?string $collection): ClientIdPolicy
    {
        $post = $collection === null ? null : $this->document->pathItem($collection)?->find('post');
        $data = $post === null ? null : $this->requestBodySchema($post)?->path('properties', 'data');
        if ($data === null) {
            return ClientIdPolicy::Forbidden;
        }

        $id = $data->path('properties', 'id');
        if ($id === null || $id->isFalse()) {
            return ClientIdPolicy::Forbidden;
        }

        return \in_array('id', $data->find('required')?->strings() ?? [], true)
            ? ClientIdPolicy::Required
            : ClientIdPolicy::Optional;
    }

    /**
     * The operations the type advertises.
     *
     * The related and relationship reads are `{rel}` templates, for the by-name door
     * (`_rel('tracks')`) where the relation is not known until runtime; their statuses are the
     * union over the concrete relation paths, which is the honest answer when any of them could
     * be the one dispatched. A generated per-relation method reads its own endpoint off
     * {@see RelationDescriptor} instead, where the statuses are exact.
     *
     * @return array<string, OperationDescriptor>
     */
    private function operations(string $collection): array
    {
        $out = [];
        $item = $this->document->pathItem($collection);
        $this->addOperation($out, OperationKind::FetchMany, $item, 'get', $collection);
        $this->addOperation($out, OperationKind::Create, $item, 'post', $collection);

        $single = $collection . '/{id}';
        $singleItem = $this->document->pathItem($single);
        $this->addOperation($out, OperationKind::FetchOne, $singleItem, 'get', $single);
        $this->addOperation($out, OperationKind::Update, $singleItem, 'patch', $single);
        $this->addOperation($out, OperationKind::Delete, $singleItem, 'delete', $single);

        $related = $this->relationReadStatuses($collection, false);
        if ($related !== null) {
            $out[OperationKind::FetchRelated->value] = new OperationDescriptor($collection . '/{id}/{rel}', 'GET', $related, null);
        }
        $relationship = $this->relationReadStatuses($collection, true);
        if ($relationship !== null) {
            $out[OperationKind::FetchRelationship->value] = new OperationDescriptor($collection . '/{id}/relationships/{rel}', 'GET', $relationship, null);
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * @param array<string, OperationDescriptor> $out
     */
    private function addOperation(array &$out, OperationKind $kind, ?Node $item, string $method, string $path): void
    {
        $operation = $this->operationAt($item, $method, $path);
        if ($operation !== null) {
            $out[$kind->value] = $operation;
        }
    }

    /** One method on a path item, with the error statuses it declares. Null when unadvertised. */
    private function operationAt(?Node $item, string $method, string $path): ?OperationDescriptor
    {
        $operation = $item?->find($method);

        return $operation === null
            ? null
            : new OperationDescriptor($path, \strtoupper($method), $this->errorStatuses($operation), $this->projection($operation), $this->examples($operation), $this->scopes($operation));
    }

    /**
     * The names of the document's `oauth2` security schemes.
     *
     * @return list<string>
     */
    private function oauth2Schemes(): array
    {
        $out = [];
        foreach ($this->document->root()->path('components', 'securitySchemes')?->members() ?? [] as $name => $scheme) {
            if ($scheme->find('type')?->string() === 'oauth2') {
                $out[] = (string) $name;
            }
        }

        return $out;
    }

    /**
     * The OAuth scopes an operation needs: what its `security` lists under an `oauth2` scheme,
     * from contract 16. An operation without its own `security` inherits the
     * document's. Null when nothing names a scope, so an older document reads as it did.
     *
     * Core fills the same scopes into every requirement that names the scheme, so taking them
     * across requirements in order, once each, reads the one set it means.
     *
     * @return list<string>|null
     */
    private function scopes(Node $operation): ?array
    {
        if ($this->scopeSchemes === []) {
            return null;
        }

        $scopes = [];
        foreach (($operation->find('security') ?? $this->document->root()->find('security'))?->items() ?? [] as $requirement) {
            foreach ($this->scopeSchemes as $scheme) {
                foreach ($requirement->find($scheme)?->strings() ?? [] as $scope) {
                    if (!\in_array($scope, $scopes, true)) {
                        $scopes[] = $scope;
                    }
                }
            }
        }

        return $scopes === [] ? null : $scopes;
    }

    /**
     * The `example` on the operation's JSON:API request body and on each success response, which
     * a contract-12 document carries. Null when it carries none of them.
     */
    private function examples(Node $operation): ?OperationExamples
    {
        $request = $this->jsonApiExample($operation->path('requestBody', 'content'));

        $responses = [];
        foreach ($operation->find('responses')?->members() ?? [] as $code => $response) {
            if (\preg_match('/^2\d\d$/', (string) $code) !== 1) {
                continue;
            }
            // A shared response is a `$ref` with no content of its own; success responses are inline.
            $example = $this->jsonApiExample($response->find('content'));
            if ($example !== null) {
                $responses[(int) $code] = $example;
            }
        }
        \ksort($responses);

        return $request === null && $responses === [] ? null : new OperationExamples($request, $responses);
    }

    /**
     * The example on the JSON:API media type of a `content` map, extension parameters and all.
     * A JSON:API document is an object, so an example that is not one is malformed.
     *
     * @return array<array-key, mixed>|null
     */
    private function jsonApiExample(?Node $content): ?array
    {
        foreach ($content?->members() ?? [] as $media => $body) {
            if (!\str_starts_with((string) $media, self::JSON_API_MEDIA_TYPE)) {
                continue;
            }
            $example = $body->find('example');
            if ($example === null) {
                continue;
            }
            if (!$example->isObject()) {
                throw SpecException::wrongType($example->pointer(), 'a JSON:API document', $example->raw());
            }
            $value = $example->raw();
            \assert(\is_array($value));

            return $value;
        }

        return null;
    }

    /**
     * The union of error statuses across a type's concrete related (or relationship) reads, or
     * null when it exposes none at all.
     *
     * @return list<int>|null
     */
    private function relationReadStatuses(string $collection, bool $linkage): ?array
    {
        $prefix = $collection . '/{id}/' . ($linkage ? 'relationships/' : '');
        $found = false;
        $statuses = [];

        foreach ($this->document->paths() as $key => $item) {
            $path = (string) $key;
            if (!\str_starts_with($path, $prefix)) {
                continue;
            }
            $rest = \substr($path, \strlen($prefix));
            if ($rest === '' || \str_contains($rest, '/') || \str_starts_with($rest, '-')) {
                continue;
            }
            $get = $item->find('get');
            if ($get === null) {
                continue;
            }
            $found = true;
            $statuses = [...$statuses, ...$this->errorStatuses($get)];
        }

        if (!$found) {
            return null;
        }

        $statuses = \array_values(\array_unique($statuses));
        \sort($statuses);

        return $statuses;
    }

    /**
     * The error statuses an operation declares, which is what a generated method's `@throws`
     * list is built from.
     *
     * @return list<int>
     */
    private function errorStatuses(Node $operation): array
    {
        $statuses = [];
        foreach (\array_keys($operation->find('responses')?->members() ?? []) as $code) {
            if (\preg_match('/^[45]\d\d$/', (string) $code) === 1) {
                $statuses[] = (int) $code;
            }
        }
        \sort($statuses);

        return $statuses;
    }

    /** @return array<string, ActionDescriptor> */
    private function actions(string $collection): array
    {
        $collectionPrefix = $collection . '/-actions/';
        $resourcePrefix = $collection . '/{id}/-actions/';

        $out = [];
        foreach ($this->document->paths() as $key => $item) {
            $path = (string) $key;
            if (\str_starts_with($path, $resourcePrefix)) {
                $scope = ActionScope::Resource;
                $name = \substr($path, \strlen($resourcePrefix));
            } elseif (\str_starts_with($path, $collectionPrefix)) {
                $scope = ActionScope::Collection;
                $name = \substr($path, \strlen($collectionPrefix));
            } else {
                continue;
            }

            if ($name === '' || \str_contains($name, '/')) {
                continue;
            }

            // The preferred method keeps the action's name, so a client generated before contract
            // 15 keeps compiling. From contract 15 every other method has its own operation id and
            // its own action; before it every method shared one id and only the preferred was read.
            $preferred = $this->actionMethod($item, $path);
            $preferredId = $item->get($preferred)->find('operationId')?->raw();
            $out[$name] = $this->action($name, $scope, $path, $item, $preferred);
            foreach (self::ACTION_METHODS as $method) {
                $operation = $item->find($method);
                if ($method === $preferred || $operation === null || $operation->find('operationId')?->raw() === $preferredId) {
                    continue;
                }
                $alias = self::methodActionName($name, $method);
                if (isset($out[$alias])) {
                    $this->warn(\sprintf('The %s operation of the "%s" action at %s would be named "%s", which another action already is; it is not generated.', \strtoupper($method), $name, $path, $alias));

                    continue;
                }
                $out[$alias] = $this->action($name, $scope, $path, $item, $method, $alias);
            }
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    private function action(string $name, ActionScope $scope, string $path, Node $item, string $method, ?string $alias = null): ActionDescriptor
    {
        $operation = $item->get($method);

        $content = $operation->path('requestBody', 'content');
        $mediaTypes = \array_map(\strval(...), \array_keys($content?->members() ?? []));
        $input = match (true) {
            $mediaTypes === [] => ActionInput::None,
            \in_array(self::JSON_API_MEDIA_TYPE, $mediaTypes, true) => ActionInput::Document,
            default => ActionInput::Raw,
        };

        [$response, $locationType] = $this->actionResponse($operation);
        $output = match (true) {
            $response === null => ActionOutput::None,
            $this->document->refName($response) === self::META_DOCUMENT => ActionOutput::Meta,
            default => ActionOutput::Document,
        };

        $sent = $input === ActionInput::Document ? $this->documentPrimary($this->requestBodySchema($operation)) : null;
        $returned = $output === ActionOutput::Document ? $this->documentPrimary($response) : null;

        // A body for a type the server doesn't register, or one with no create forms, is an open
        // schema with nothing to generate a signature from.
        if ($input === ActionInput::Document && ($sent === null || !\in_array($sent['type'], $this->wireTypes, true))) {
            throw SpecException::malformed(
                $operation->path('requestBody', 'content', self::JSON_API_MEDIA_TYPE, 'schema')?->pointer() ?? $path,
                \sprintf('to be a create document of a type the document describes, as the body of the "%s" action', $name),
            );
        }
        if ($output === ActionOutput::Document && ($returned === null || !\in_array($returned['type'], $this->wireTypes, true))) {
            throw SpecException::malformed(
                $response->pointer(),
                \sprintf('to be a document of a type the document describes, as the response of the "%s" action', $name),
            );
        }
        $contentType = $input === ActionInput::Raw
            ? (\array_values(\array_filter($mediaTypes, static fn(string $media): bool => $media !== self::JSON_API_MEDIA_TYPE))[0] ?? null)
            : null;

        $requests = [];
        $rawRequests = \array_values(\array_filter($mediaTypes, static fn(string $media): bool => !\str_starts_with($media, self::JSON_API_MEDIA_TYPE)));
        if ($input === ActionInput::Document && $rawRequests !== []) {
            $this->warn(\sprintf(
                'The "%s" action at %s also accepts %s; only its JSON:API body is generated.',
                $name,
                $path,
                \implode(', ', $rawRequests),
            ));
        } elseif ($input === ActionInput::Raw && (\count($rawRequests) > 1 || \str_contains($rawRequests[0] ?? '', '*') || RawMediaKind::formOf($rawRequests[0] ?? '') !== null)) {
            foreach ($rawRequests as $media) {
                $form = RawMediaKind::formOf($media);
                $schema = $content?->path($media, 'schema');
                $example = $content?->path($media, 'example')?->raw();
                $requests[] = $form === null ? $this->rawMedia($media, $schema, $example) : $this->formMedia($media, $form, $schema, $example);
            }
        }

        $responses = [];
        foreach ($operation->find('responses')?->members() ?? [] as $code => $response) {
            if (!\str_starts_with((string) $code, '2')) {
                continue;
            }
            foreach ($response->find('content')?->members() ?? [] as $media => $body) {
                $media = (string) $media;
                if (\str_starts_with($media, self::JSON_API_MEDIA_TYPE) || isset($responses[$media])) {
                    continue;
                }
                $responses[$media] = $this->rawMedia($media, $body->find('schema'), $body->find('example')?->raw());
            }
        }

        return new ActionDescriptor(
            $name,
            $scope,
            $path,
            \strtoupper($method),
            $input,
            $output,
            $sent === null ? null : $sent['type'],
            $returned === null ? null : $returned['type'],
            $returned === null ? null : $returned['cardinality'],
            $contentType,
            $this->errorStatuses($operation),
            $this->projection($operation),
            $input === ActionInput::None || $operation->path('requestBody', 'required')?->bool() === true,
            self::authored($operation->find('summary')?->string(), '/^Invoke the `[^`]*` action$/'),
            self::authored($operation->find('description')?->string(), '/^Invokes the `[^`]*` custom action on [^\n]*\.$/'),
            \array_values($responses),
            $requests,
            $this->examples($operation),
            $alias,
            $this->safety($operation),
            $this->scopes($operation),
            $locationType,
        );
    }

    /**
     * An action's JSON:API success body, apart from a contract-17 location document, and the
     * media type that location document declares.
     *
     * A location document is an `allOf` of the `LocationDocument` component and the declared
     * `type` const, either the whole `200` or one branch of a `oneOf` beside the body the action
     * also declares. Without one this is the first 2xx JSON:API body, as it always was.
     *
     * @return array{Node|null, string|null}
     */
    private function actionResponse(Node $operation): array
    {
        $body = null;
        $locationType = null;
        foreach ($operation->find('responses')?->members() ?? [] as $code => $response) {
            if (!\str_starts_with((string) $code, '2')) {
                continue;
            }
            $schema = $response->path('content', self::JSON_API_MEDIA_TYPE, 'schema');
            if ($schema === null) {
                continue;
            }

            $branches = $schema->find('oneOf')?->items() ?? [$schema];
            $others = [];
            foreach ($branches as $branch) {
                $type = $this->locationType($branch);
                if ($type === null) {
                    $others[] = $branch;
                } else {
                    $locationType ??= $type;
                }
            }
            if ($others === [] || $body !== null) {
                continue;
            }
            if (\count($others) === \count($branches)) {
                // No location branch, so the schema is read whole, as it always was.
                $body = $schema;
            } elseif (\count($others) === 1) {
                $body = $others[0];
            } else {
                throw SpecException::malformed($schema->pointer(), 'to offer one JSON:API document beside the location document');
            }
        }

        return [$body, $locationType];
    }

    /**
     * The media type a location document declares its URL serves, or null when the schema is not
     * a location document.
     */
    private function locationType(Node $schema): ?string
    {
        $parts = $schema->find('allOf')?->items() ?? [$schema];
        $location = false;
        $type = null;
        foreach ($parts as $part) {
            if ($this->document->refName($part) === self::LOCATION_DOCUMENT) {
                $location = true;

                continue;
            }
            $const = $part->path('properties', 'links', 'properties', 'location', 'properties', 'type', 'const');
            if ($const !== null) {
                $type = $const->string();
            }
        }
        if (!$location) {
            return null;
        }

        return $type ?? throw SpecException::malformed($schema->pointer(), 'to narrow the location document\'s links.location.type to the media type the action declares');
    }

    /**
     * The flags an action operation's `x-safety` declares, from contract 15. Each is a boolean
     * when present; an operation with no `x-safety`, or one declaring none of the three, has none.
     */
    private function safety(Node $operation): ?ActionSafety
    {
        $safety = $operation->find('x-safety');
        if ($safety === null) {
            return null;
        }
        if (!$safety->isObject()) {
            throw SpecException::wrongType($safety->pointer(), 'an object', $safety->raw());
        }

        $flags = \array_map(static fn(string $key): ?bool => $safety->find($key)?->bool(), ['readOnly', 'destructive', 'idempotent']);

        return $flags === [null, null, null] ? null : new ActionSafety(...$flags);
    }

    /** The operation's prose, or null when it is the projector's default, which names only the action. */
    private static function authored(?string $prose, string $default): ?string
    {
        return $prose === null || \trim($prose) === '' || \preg_match($default, $prose) === 1 ? null : $prose;
    }

    /**
     * Classify one raw media type of an action. The schema decides first: `format: binary` is
     * bytes. A JSON media type is JSON, typed by its schema; a string schema or a `text/*` type
     * without one is text; anything else is bytes.
     */
    private function rawMedia(string $media, ?Node $schema, mixed $example = null): RawMediaDescriptor
    {
        $target = $schema === null ? null : $this->document->dereference($schema);
        $essence = \strtolower(\trim(\explode(';', $media, 2)[0]));
        $json = $essence === 'application/json' || \str_ends_with($essence, '+json');

        $kind = match (true) {
            $target?->find('format')?->isString() === true && $target->get('format')->string() === 'binary' => RawMediaKind::Binary,
            $json => RawMediaKind::Json,
            $target !== null && \in_array('string', $this->declaredTypes($target), true), $target === null && \str_starts_with($essence, 'text/') => RawMediaKind::Text,
            default => RawMediaKind::Binary,
        };

        return new RawMediaDescriptor(
            $media,
            $kind,
            $kind === RawMediaKind::Json && $schema !== null ? $this->valueSchema($schema) : null,
            $kind === RawMediaKind::Json && $schema !== null ? $this->document->refName($schema) : null,
            example: $example,
        );
    }

    /**
     * A form request body, narrowed to its schema's `properties` when it lists any. Without them
     * the form is a generic field array and keeps no schema.
     */
    private function formMedia(string $media, RawMediaKind $kind, ?Node $schema, mixed $example = null): RawMediaDescriptor
    {
        $target = $schema === null ? null : $this->document->dereference($schema);
        $properties = $target?->find('properties');
        if ($target === null || $properties === null || $properties->members() === []) {
            return new RawMediaDescriptor($media, $kind, example: $example);
        }

        $additional = $target->find('additionalProperties');

        return new RawMediaDescriptor(
            $media,
            $kind,
            $this->valueSchema($target, false),
            additional: match (true) {
                $additional === null => false,
                $additional->isObject() => $this->valueSchema($additional, false),
                default => $additional->bool(),
            },
            example: $example,
        );
    }

    /**
     * The name a multi-method action's other methods go under: the action's name and the method,
     * joined the way the name joins its words, so `export` gives `exportGet` and `bulk-import`
     * gives `bulk-import-get`. Core refuses action ids that differ only in case or punctuation, so
     * it can't be another action's name.
     */
    private static function methodActionName(string $name, string $method): string
    {
        return \preg_match('/[-_.]/', $name, $separator) === 1 ? $name . $separator[0] . $method : $name . \ucfirst($method);
    }

    /** POST where it is advertised, else the first method that is. */
    private function actionMethod(Node $item, string $path): string
    {
        foreach (self::ACTION_METHODS as $method) {
            if ($item->find($method) !== null) {
                return $method;
            }
        }

        throw SpecException::malformed($path, 'to advertise an operation');
    }

    /**
     * The JSON:API type and cardinality of a document schema's primary `data` — a `$ref` to a
     * resource, an inline object carrying its own `type` const, or an array of either.
     *
     * @return array{type: string, cardinality: Cardinality}|null
     */
    private function documentPrimary(?Node $schema): ?array
    {
        $data = $schema === null ? null : $this->document->dereference($schema)->path('properties', 'data');
        if ($data === null) {
            return null;
        }

        $items = $data->find('items');
        $node = $items ?? $data;
        $type = $this->document->dereference($node)->path('properties', 'type', 'const')?->string();

        return $type === null
            ? null
            : ['type' => $type, 'cardinality' => $items === null ? Cardinality::One : Cardinality::Many];
    }

    private function requestBodySchema(Node $operation): ?Node
    {
        $schema = $operation->path('requestBody', 'content', self::JSON_API_MEDIA_TYPE, 'schema');

        return $schema === null ? null : $this->document->dereference($schema);
    }

    /** The JSON:API body schema of an operation's first 2xx response, dereferenced. */
    private function okResponseSchema(Node $operation): ?Node
    {
        foreach ($operation->find('responses')?->members() ?? [] as $code => $response) {
            if (!\str_starts_with((string) $code, '2')) {
                continue;
            }
            $schema = $response->path('content', self::JSON_API_MEDIA_TYPE, 'schema');
            if ($schema !== null) {
                return $schema;
            }
        }

        return null;
    }

    /** @return array<string, EnumDescriptor> */
    private function enums(): array
    {
        $out = [];
        foreach ($this->document->schemas() as $key => $schema) {
            $name = (string) $key;
            $values = $schema->find('enum');
            if ($values === null || $this->document->refName($schema) !== null) {
                continue;
            }

            self::assertClassName($name);
            $varNames = $schema->find('x-enum-varnames')?->strings() ?? [];
            $descriptions = $schema->find('x-enum-descriptions')?->strings() ?? [];

            $cases = [];
            foreach ($values->enumValues() as $index => $value) {
                $varName = $varNames[$index] ?? null;
                if ($varName !== null && !Identifier::isConstantName($varName)) {
                    $this->warn(\sprintf(
                        'components.schemas.%s.x-enum-varnames.%d is %s, which cannot name a PHP enum case, so the case is named from its value instead.',
                        $name,
                        $index,
                        \json_encode($varName, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE),
                    ));
                    $varName = null;
                }
                $cases[] = new EnumCase($value, $varName, $descriptions[$index] ?? null);
            }

            $out[$name] = new EnumDescriptor($name, $cases, $schema->find('description')?->string());
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    /**
     * The atomic endpoint, found by the `ext` parameter on its request media type. Null when the
     * server advertises none, which is what keeps `atomic()` from being generated at all.
     */
    private function atomic(): ?AtomicDescriptor
    {
        foreach ($this->document->paths() as $key => $item) {
            $path = (string) $key;
            $content = $item->path('post', 'requestBody', 'content');
            foreach (\array_keys($content?->members() ?? []) as $mediaType) {
                $mediaType = (string) $mediaType;
                if (\str_starts_with($mediaType, self::JSON_API_MEDIA_TYPE) && \str_contains($mediaType, self::ATOMIC_EXT)) {
                    return new AtomicDescriptor($path, $mediaType, $this->scopes($item->get('post')));
                }
            }
        }

        return null;
    }
}
