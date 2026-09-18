<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The part of a JSON Schema fragment the codegen acts on, for the places a document declares a
 * value shape rather than a named type — today, filter values.
 *
 * Deliberately a subset. Anything outside these members is not read, so a schema that carries
 * more than this loses the extra rather than smuggling an unvalidated array through the model.
 */
final class ValueSchema
{
    /**
     * @param list<string>               $types      the declared JSON types, `null` among them when nullable
     * @param array<string, ValueSchema> $properties object members, sorted by name
     * @param list<string>               $required   required member names, sorted
     * @param list<string>               $enum       permitted string values
     */
    public function __construct(
        public readonly array $types,
        public readonly ?string $format,
        public readonly array $properties,
        public readonly ?self $items,
        public readonly array $required,
        public readonly array $enum,
    ) {}
}
