<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * Renders a descriptor to JSON, deterministically.
 *
 * Maps arrive from the builder already sorted by key and every value object is written in a
 * fixed member order, so the same document always produces byte-identical output. That is what
 * makes the golden file a review artifact: a diff is a change in what the codegen understood,
 * never a reordering.
 *
 * Absent values are written as `null` or an empty collection rather than being omitted, so the
 * diff for a capability appearing or disappearing is a changed value, not a moved key.
 */
final class DescriptorSerializer
{
    public static function toJson(ApiDescriptor $descriptor): string
    {
        return \json_encode(
            self::toArray($descriptor),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR,
        ) . "\n";
    }

    /** @return array<string, mixed> */
    public static function toArray(ApiDescriptor $descriptor): array
    {
        return [
            'contract' => $descriptor->contract,
            'atomic' => $descriptor->atomic === null ? null : [
                'path' => $descriptor->atomic->path,
                'mediaType' => $descriptor->atomic->mediaType,
            ],
            'enums' => \array_map(self::enum(...), $descriptor->enums),
            'resources' => \array_map(self::resource(...), $descriptor->resources),
        ];
    }

    /** @return array<string, mixed> */
    private static function enum(EnumDescriptor $enum): array
    {
        return [
            'name' => $enum->name,
            'description' => $enum->description,
            'cases' => \array_map(static fn(EnumCase $case): array => [
                'value' => $case->value,
                'name' => $case->name,
                'description' => $case->description,
            ], $enum->cases),
        ];
    }

    /** @return array<string, mixed> */
    private static function resource(ResourceDescriptor $resource): array
    {
        return [
            'type' => $resource->type,
            'paginator' => self::paginator($resource->paginator),
            'clientId' => $resource->clientId->value,
            'countable' => self::countable($resource->countable),
            'attributes' => \array_map(static fn(AttributeDescriptor $attribute): array => [
                'format' => $attribute->format,
                'nullable' => $attribute->nullable,
                'enum' => $attribute->enum,
                'schema' => self::valueSchema($attribute->schema),
            ], $resource->attributes),
            'relations' => \array_map(self::relation(...), $resource->relations),
            'operations' => \array_map(self::operation(...), $resource->operations),
            'includable' => $resource->includable,
            'sortable' => $resource->sortable,
            'filterable' => \array_map(static fn(FilterDescriptor $filter): array => [
                'schema' => self::valueSchema($filter->schema),
            ], $resource->filterable),
            'actions' => \array_map(self::action(...), $resource->actions),
        ];
    }

    /** @return array<string, mixed> */
    private static function relation(RelationDescriptor $relation): array
    {
        return [
            'cardinality' => $relation->cardinality->value,
            'types' => $relation->types,
            'related' => self::operation($relation->relatedRead),
            'relationship' => self::operation($relation->relationshipRead),
            'mutations' => \array_map(self::operation(...), $relation->mutations),
            'paginator' => $relation->paginator === null ? null : self::paginator($relation->paginator),
            'countable' => self::countable($relation->countable),
            'pivot' => $relation->pivot,
            'pivotFields' => \array_map(static fn(PivotFieldDescriptor $field): array => [
                'format' => $field->format,
                'nullable' => $field->nullable,
                'enum' => $field->enum,
                'schema' => self::valueSchema($field->schema),
                'readOnly' => $field->readOnly,
                'required' => $field->required,
            ], $relation->pivotFields),
        ];
    }

    /** @return array<string, mixed> */
    private static function action(ActionDescriptor $action): array
    {
        return [
            'scope' => $action->scope->value,
            'path' => $action->path,
            'method' => $action->method,
            'input' => $action->input->value,
            'inputType' => $action->inputType,
            'contentType' => $action->contentType,
            'output' => $action->output->value,
            'outputType' => $action->outputType,
            'outputCardinality' => $action->outputCardinality?->value,
            'errorStatuses' => $action->errorStatuses,
        ];
    }

    /** @return array<string, mixed> */
    private static function paginator(PaginatorDescriptor $paginator): array
    {
        return ['kind' => $paginator->kind->value, 'parameters' => $paginator->parameters];
    }

    /** @return ($operation is null ? null : array<string, mixed>) */
    private static function operation(?OperationDescriptor $operation): ?array
    {
        return $operation === null ? null : [
            'path' => $operation->path,
            'method' => $operation->method,
            'errorStatuses' => $operation->errorStatuses,
        ];
    }

    /** @return array<string, mixed>|null */
    private static function countable(?CountableDescriptor $countable): ?array
    {
        return $countable === null ? null : ['tokens' => $countable->tokens, 'profile' => $countable->profile];
    }

    /** @return array<string, mixed>|null */
    private static function valueSchema(?ValueSchema $schema): ?array
    {
        return $schema === null ? null : [
            'types' => $schema->types,
            'format' => $schema->format,
            'enum' => $schema->enum,
            'required' => $schema->required,
            'properties' => \array_map(self::valueSchema(...), $schema->properties),
            'items' => self::valueSchema($schema->items),
        ];
    }
}
