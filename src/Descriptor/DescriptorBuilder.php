<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

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

    /** The methods an action may be advertised under, in the order one is picked. */
    private const array ACTION_METHODS = ['post', 'get', 'put', 'patch', 'delete'];

    /** @var array<string, Node> resource schema name => schema */
    private array $resourceSchemas = [];

    /** @var array<string, string> resource schema name => JSON:API type */
    private array $wireTypes = [];

    /** @var array<string, string> JSON:API type => collection path */
    private array $collectionPaths = [];

    /** @var array<string, PaginatorKind> JSON:API type => its collection's paginator */
    private array $typePaginators = [];

    private function __construct(private readonly SpecDocument $document) {}

    public static function build(SpecDocument $document): ApiDescriptor
    {
        return (new self($document))->descriptor();
    }

    private function descriptor(): ApiDescriptor
    {
        $this->collectResourceSchemas();
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
                ? PaginatorKind::None
                : $this->paginatorKind($this->document->pathItem($path)?->find('get'));
        }
    }

    private function resource(string $type, Node $schema): ResourceDescriptor
    {
        $collection = $this->collectionPaths[$type] ?? null;
        $list = $collection === null ? null : $this->document->pathItem($collection)?->find('get');

        return new ResourceDescriptor(
            $type,
            $this->attributes($schema),
            $this->relations($schema, $collection),
            $collection === null ? [] : $this->operations($collection),
            $this->typePaginators[$type] ?? PaginatorKind::None,
            $this->clientIdPolicy($collection),
            $this->countable($list),
            $this->tokenEnum($list, 'include'),
            $this->tokenEnum($list, 'sort'),
            $this->filterable($list),
            $collection === null ? [] : $this->actions($collection),
        );
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
                $this->compositeSchema($attribute, $hint['format']),
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
        $reference = $this->document->refName($schema);
        $target = $reference === null ? $schema : $this->document->schema($reference);
        $isEnum = $target->find('enum') !== null;

        $format = $target->find('format')?->string()
            ?? $this->jsonType($target)
            ?? ($isEnum ? 'string' : 'unknown');

        return [
            'format' => $format,
            'enum' => $isEnum ? $reference : null,
            'nullable' => $this->admitsNull($schema) || $this->admitsNull($target),
        ];
    }

    /**
     * The whole value schema, for the values a format hint cannot describe on its own: an
     * object's members and an array's item type. Null for a scalar, where the hint says it all.
     */
    private function compositeSchema(Node $schema, string $format): ?ValueSchema
    {
        return \in_array($format, ['object', 'array'], true) ? $this->valueSchema($schema) : null;
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

    private function admitsNull(Node $schema): bool
    {
        return \in_array('null', $this->declaredTypes($schema), true);
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

    /** @return array<string, RelationDescriptor> */
    private function relations(Node $schema, ?string $collection): array
    {
        $properties = $schema->path('properties', 'relationships', 'properties');
        if ($properties === null) {
            return [];
        }

        $out = [];
        foreach ($properties->members() as $key => $property) {
            $name = (string) $key;
            $out[$name] = $this->relation($name, $this->document->dereference($property), $collection);
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    private function relation(string $name, Node $component, ?string $collection): RelationDescriptor
    {
        $data = $component->get('properties')->get('data');
        $cardinality = $data->find('type')?->string() === 'array' ? Cardinality::Many : Cardinality::One;
        $linkage = $this->linkage($cardinality === Cardinality::Many ? $data->get('items') : $data);

        $relatedPath = $collection . '/{id}/' . $name;
        $relationshipPath = $collection . '/{id}/relationships/' . $name;
        $related = $collection === null ? null : $this->document->pathItem($relatedPath);
        $relationship = $collection === null ? null : $this->document->pathItem($relationshipPath);
        $relatedGet = $related?->find('get');
        $relationshipGet = $relationship?->find('get');

        return new RelationDescriptor(
            $name,
            $cardinality,
            $linkage['types'],
            $linkage['pivot'],
            $linkage['pivotFields'],
            $this->operationAt($related, 'get', $relatedPath),
            $this->operationAt($relationship, 'get', $relationshipPath),
            $this->relationMutations($relationship, $cardinality, $relationshipPath),
            $this->countable($relatedGet) ?? $this->countable($relationshipGet),
            $this->relationPaginator($cardinality, $linkage['types'], $relatedGet, $relationshipGet),
        );
    }

    /**
     * The related types a linkage names, plus its pivot shape.
     *
     * Four shapes reach here: a `$ref` to an identifier, an `allOf` pairing one with a
     * `meta.pivot` block, an `anyOf` (a nullable to-one, or a polymorphic set), and an `anyOf`
     * nested one level deeper for a polymorphic to-one. Anything else is a linkage grammar this
     * codegen has not been taught, and errors rather than being dropped.
     *
     * @return array{types: list<string>, pivot: bool, pivotFields: array<string, PivotFieldDescriptor>}
     */
    private function linkage(Node $node): array
    {
        if ($this->document->refName($node) !== null) {
            return ['types' => [$this->identifierType($node)], 'pivot' => false, 'pivotFields' => []];
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
                $fields = [...$fields, ...$this->pivotFields($entry)];
            }
            if ($types === []) {
                throw SpecException::malformed($allOf->pointer(), 'to reference a resource identifier');
            }
            \ksort($fields, \SORT_STRING);

            return ['types' => $types, 'pivot' => true, 'pivotFields' => $fields];
        }

        $anyOf = $node->find('anyOf');
        if ($anyOf !== null) {
            $types = [];
            $pivot = false;
            $fields = [];
            foreach ($anyOf->items() as $entry) {
                if ($entry->find('type')?->string() === 'null') {
                    continue;
                }
                $nested = $this->linkage($entry);
                $types = [...$types, ...$nested['types']];
                $pivot = $pivot || $nested['pivot'];
                $fields = [...$fields, ...$nested['pivotFields']];
            }
            if ($types === []) {
                throw SpecException::malformed($anyOf->pointer(), 'to reference at least one resource identifier');
            }

            return ['types' => $types, 'pivot' => $pivot, 'pivotFields' => $fields];
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
     * flag and its membership of the pivot object's `required` list.
     *
     * @return array<string, PivotFieldDescriptor>
     */
    private function pivotFields(Node $entry): array
    {
        $pivot = $entry->path('properties', 'meta', 'properties', 'pivot');
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
                $this->compositeSchema($field, $hint['format']),
                $field->find('readOnly')?->bool() === true,
                \in_array($name, $required, true),
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
     * A relation's own paginator, carried only when its related endpoint paginates differently
     * from the related type's collection. Null means the two agree, and the related type's kind
     * applies.
     *
     * @param list<string> $types
     */
    private function relationPaginator(Cardinality $cardinality, array $types, ?Node $relatedGet, ?Node $relationshipGet): ?PaginatorKind
    {
        if ($cardinality === Cardinality::One) {
            return null;
        }

        $kind = $this->paginatorKind($relatedGet);
        if ($kind === PaginatorKind::None) {
            $kind = $this->paginatorKind($relationshipGet);
        }

        $relatedType = $types[0] ?? null;
        $typeKind = $relatedType === null ? PaginatorKind::None : ($this->typePaginators[$relatedType] ?? PaginatorKind::None);

        return $kind !== PaginatorKind::None && $kind !== $typeKind ? $kind : null;
    }

    /**
     * The paginator an operation advertises, read from its `page[…]` parameter names. Page
     * parameters that match no known kind are a drift this codegen would otherwise turn into a
     * silently unpaginated client, so they error.
     */
    private function paginatorKind(?Node $operation): PaginatorKind
    {
        if ($operation === null) {
            return PaginatorKind::None;
        }

        $names = $this->queryParameterNames($operation);
        $has = static fn(string $name): bool => \in_array($name, $names, true);

        if ($has('page[number]') && $has('page[size]')) {
            return PaginatorKind::Page;
        }
        if ($has('page[offset]') && $has('page[limit]')) {
            return PaginatorKind::Offset;
        }
        // Cursor navigation is link-driven, so either bound is enough to name the kind.
        if ($has('page[after]') || $has('page[before]')) {
            return PaginatorKind::Cursor;
        }

        foreach ($names as $name) {
            if (\str_starts_with($name, 'page[')) {
                throw SpecException::malformed(
                    $operation->pointer() . '.parameters',
                    'to declare page parameters matching a known paginator, got ' . \implode(', ', $names),
                );
            }
        }

        return PaginatorKind::None;
    }

    /** @return list<string> */
    private function queryParameterNames(Node $operation): array
    {
        $names = [];
        foreach ($operation->find('parameters')?->items() ?? [] as $parameter) {
            $name = $parameter->find('name')?->string();
            if ($name !== null) {
                $names[] = $name;
            }
        }

        return $names;
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

    /** @return array<string, FilterDescriptor> */
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
        \ksort($out, \SORT_STRING);

        return $out;
    }

    private function valueSchema(Node $schema): ValueSchema
    {
        $schema = $this->document->dereference($schema);

        $properties = [];
        foreach ($schema->find('properties')?->members() ?? [] as $key => $property) {
            $properties[(string) $key] = $this->valueSchema($property);
        }
        \ksort($properties, \SORT_STRING);

        $required = $schema->find('required')?->strings() ?? [];
        \sort($required, \SORT_STRING);

        $items = $schema->find('items');

        return new ValueSchema(
            $this->declaredTypes($schema),
            $schema->find('format')?->string(),
            $properties,
            $items === null ? null : $this->valueSchema($items),
            $required,
            $schema->find('enum')?->strings() ?? [],
        );
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
            $out[OperationKind::FetchRelated->value] = new OperationDescriptor($collection . '/{id}/{rel}', 'GET', $related);
        }
        $relationship = $this->relationReadStatuses($collection, true);
        if ($relationship !== null) {
            $out[OperationKind::FetchRelationship->value] = new OperationDescriptor($collection . '/{id}/relationships/{rel}', 'GET', $relationship);
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
            : new OperationDescriptor($path, \strtoupper($method), $this->errorStatuses($operation));
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

            $out[$name] = $this->action($name, $scope, $path, $item);
        }
        \ksort($out, \SORT_STRING);

        return $out;
    }

    private function action(string $name, ActionScope $scope, string $path, Node $item): ActionDescriptor
    {
        $method = $this->actionMethod($item, $path);
        $operation = $item->get($method);

        $content = $operation->path('requestBody', 'content');
        $mediaTypes = \array_map(\strval(...), \array_keys($content?->members() ?? []));
        $input = match (true) {
            $mediaTypes === [] => ActionInput::None,
            \in_array(self::JSON_API_MEDIA_TYPE, $mediaTypes, true) => ActionInput::Document,
            default => ActionInput::Raw,
        };

        $response = $this->okResponseSchema($operation);
        $output = match (true) {
            $response === null => ActionOutput::None,
            $this->document->refName($response) === self::META_DOCUMENT => ActionOutput::Meta,
            default => ActionOutput::Document,
        };

        $sent = $input === ActionInput::Document ? $this->documentPrimary($this->requestBodySchema($operation)) : null;
        $returned = $output === ActionOutput::Document ? $this->documentPrimary($response) : null;
        $contentType = $input === ActionInput::Raw
            ? (\array_values(\array_filter($mediaTypes, static fn(string $media): bool => $media !== self::JSON_API_MEDIA_TYPE))[0] ?? null)
            : null;

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
        );
    }

    /** POST where it is advertised, else the one method that is. */
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

            $varNames = $schema->find('x-enum-varnames')?->strings() ?? [];
            $descriptions = $schema->find('x-enum-descriptions')?->strings() ?? [];

            $cases = [];
            foreach ($values->strings() as $index => $value) {
                $cases[] = new EnumCase($value, $varNames[$index] ?? null, $descriptions[$index] ?? null);
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
                    return new AtomicDescriptor($path, $mediaType);
                }
            }
        }

        return null;
    }
}
