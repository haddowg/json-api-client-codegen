<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * Everything the emitters need about one resource type.
 *
 * The maps are keyed and sorted by name so a regeneration diff shows only what genuinely
 * changed. `$filterable` is the exception: like `$includable` and `$sortable` it keeps the
 * document's order, which is stable across regenerations too.
 */
final class ResourceDescriptor
{
    /**
     * @param array<string, AttributeDescriptor>      $attributes
     * @param array<string, WriteAttributeDescriptor> $createAttributes attributes the create document accepts
     * @param array<string, WriteAttributeDescriptor> $updateAttributes attributes the update document accepts
     * @param array<string, RelationDescriptor>       $relations
     * @param array<string, OperationDescriptor> $operations keyed by {@see OperationKind} value
     * @param list<string>                       $includable relation paths accepted in `include`, nested ones among them
     * @param list<string>                       $sortable   signed sort tokens, both directions
     * @param array<string, FilterDescriptor>    $filterable in document order
     * @param array<string, ActionDescriptor>    $actions
     * @param list<string>                       $createRelations relations the create document accepts, in document order
     * @param list<string>                       $updateRelations relations the update document accepts, in document order
     * @param list<string>                       $memberOrder     every attribute then every relation, in the order the resource schema declares them
     * @param array<string, RelationDescriptor>  $writeOnlyRelations relations a write accepts that no read shows, keyed and sorted by name
     */
    public function __construct(
        public readonly string $type,
        public readonly array $attributes,
        public readonly array $createAttributes,
        public readonly array $updateAttributes,
        public readonly array $relations,
        public readonly array $operations,
        public readonly PaginatorDescriptor $paginator,
        public readonly ClientIdPolicy $clientId,
        public readonly ?CountableDescriptor $countable,
        public readonly array $includable,
        public readonly array $sortable,
        public readonly array $filterable,
        public readonly array $actions,
        public readonly array $createRelations = [],
        public readonly array $updateRelations = [],
        public readonly array $memberOrder = [],
        public readonly array $writeOnlyRelations = [],
    ) {}

    /**
     * `$names` in the order the document declares them, for generated code whose member order
     * should follow the document rather than the sorted maps. Names the resource schema does
     * not declare follow, in the order given.
     *
     * @param list<string> $names
     *
     * @return list<string>
     */
    public function inDocumentOrder(array $names): array
    {
        $known = \array_values(\array_intersect($this->memberOrder, $names));

        return [...$known, ...\array_values(\array_diff($names, $known))];
    }

    public function operation(OperationKind $kind): ?OperationDescriptor
    {
        return $this->operations[$kind->value] ?? null;
    }

    public function relation(string $name): ?RelationDescriptor
    {
        return $this->relations[$name] ?? null;
    }

    /**
     * A relation a write can set: a readable one, or a write-only one, which contract 5 leaves
     * out of every read schema but still accepts in the create and update documents.
     */
    public function writableRelation(string $name): ?RelationDescriptor
    {
        return $this->relations[$name] ?? $this->writeOnlyRelations[$name] ?? null;
    }

    /**
     * Attributes present on reads but accepted by neither write — the read-only ones. They
     * belong on the resource DTO and on neither write form.
     *
     * @return list<string>
     */
    public function readOnlyAttributes(): array
    {
        return \array_values(\array_filter(
            \array_keys($this->attributes),
            fn(string $name): bool => !isset($this->createAttributes[$name]) && !isset($this->updateAttributes[$name]),
        ));
    }
}
