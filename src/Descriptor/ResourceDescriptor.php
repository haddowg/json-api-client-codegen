<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * Everything the emitters need about one resource type.
 *
 * The maps are keyed and sorted by name so a regeneration diff shows only what genuinely
 * changed.
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
     * @param array<string, FilterDescriptor>    $filterable
     * @param array<string, ActionDescriptor>    $actions
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
    ) {}

    public function operation(OperationKind $kind): ?OperationDescriptor
    {
        return $this->operations[$kind->value] ?? null;
    }

    public function relation(string $name): ?RelationDescriptor
    {
        return $this->relations[$name] ?? null;
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
