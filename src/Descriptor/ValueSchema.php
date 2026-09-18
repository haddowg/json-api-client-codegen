<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The part of a JSON Schema fragment the codegen acts on, for the places a document declares a
 * value shape rather than a named type — filter values, and attributes whose format hint cannot
 * describe them.
 *
 * Deliberately a subset. Anything outside these members is not read, so a schema that carries
 * more than this loses the extra rather than smuggling an unvalidated array through the model.
 *
 * Composition is carried rather than flattened. A schema whose members live in `anyOf` branches
 * has none of its own, and reporting that as an object with no members would be a lie an emitter
 * would act on — it would generate an empty shape and believe it.
 */
final class ValueSchema
{
    /**
     * @param list<string>               $types      the declared JSON types, `null` among them when nullable
     * @param array<string, ValueSchema> $properties object members, sorted by name
     * @param list<string>               $required   required member names, sorted
     * @param list<string>               $enum       permitted string values
     * @param list<ValueSchema>          $oneOf      exactly-one-of branches, in document order
     * @param list<ValueSchema>          $anyOf      at-least-one-of branches, in document order
     * @param list<ValueSchema>          $allOf      all-of branches, in document order
     */
    public function __construct(
        public readonly array $types,
        public readonly ?string $format,
        public readonly array $properties,
        public readonly ?self $items,
        public readonly array $required,
        public readonly array $enum,
        public readonly array $oneOf,
        public readonly array $anyOf,
        public readonly array $allOf,
        public readonly ?string $discriminator,
    ) {}

    /** True when this schema's real shape is in its branches rather than its own members. */
    public function composed(): bool
    {
        return $this->oneOf !== [] || $this->anyOf !== [] || $this->allOf !== [];
    }

    /** @return list<ValueSchema> every composition branch, whichever keyword introduced it */
    public function branches(): array
    {
        return [...$this->oneOf, ...$this->anyOf, ...$this->allOf];
    }
}
