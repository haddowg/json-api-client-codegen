<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The validation keywords one schema declares, read where they stand.
 *
 * Generator contract 5 projects a constraint only into a schema whose every value satisfies it
 * (core ADR 0155), so the read, create and update schemas of one attribute can carry different
 * keywords, and each is read from its own schema. That guarantee is what lets an emitter narrow
 * a generated type from them: a bound the server enforces on every write is a bound every read
 * returns.
 *
 * `$default` is not a constraint, but it sits beside them in the schema and only page members
 * carry one, so it travels here rather than on a structure of its own.
 */
final class Constraints
{
    public function __construct(
        public readonly int|float|null $minimum = null,
        public readonly int|float|null $maximum = null,
        public readonly int|float|null $exclusiveMinimum = null,
        public readonly int|float|null $exclusiveMaximum = null,
        public readonly int|float|null $multipleOf = null,
        public readonly ?int $minLength = null,
        public readonly ?int $maxLength = null,
        public readonly ?string $pattern = null,
        public readonly ?int $minItems = null,
        public readonly ?int $maxItems = null,
        public readonly bool $uniqueItems = false,
        public readonly ?int $minProperties = null,
        public readonly ?int $maxProperties = null,
        public readonly int|float|string|bool|null $default = null,
    ) {}

    public static function none(): self
    {
        return new self();
    }

    public function isEmpty(): bool
    {
        return $this->toArray() === [];
    }

    /**
     * This set with every keyword `$fallback` declares and this one does not.
     */
    public function orElse(self $fallback): self
    {
        return new self(
            $this->minimum ?? $fallback->minimum,
            $this->maximum ?? $fallback->maximum,
            $this->exclusiveMinimum ?? $fallback->exclusiveMinimum,
            $this->exclusiveMaximum ?? $fallback->exclusiveMaximum,
            $this->multipleOf ?? $fallback->multipleOf,
            $this->minLength ?? $fallback->minLength,
            $this->maxLength ?? $fallback->maxLength,
            $this->pattern ?? $fallback->pattern,
            $this->minItems ?? $fallback->minItems,
            $this->maxItems ?? $fallback->maxItems,
            $this->uniqueItems || $fallback->uniqueItems,
            $this->minProperties ?? $fallback->minProperties,
            $this->maxProperties ?? $fallback->maxProperties,
            $this->default ?? $fallback->default,
        );
    }

    /**
     * The declared keywords, in a fixed order, absent ones left out.
     *
     * @return array<string, int|float|string|bool>
     */
    public function toArray(): array
    {
        return \array_filter([
            'minimum' => $this->minimum,
            'maximum' => $this->maximum,
            'exclusiveMinimum' => $this->exclusiveMinimum,
            'exclusiveMaximum' => $this->exclusiveMaximum,
            'multipleOf' => $this->multipleOf,
            'minLength' => $this->minLength,
            'maxLength' => $this->maxLength,
            'pattern' => $this->pattern,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
            'uniqueItems' => $this->uniqueItems ?: null,
            'minProperties' => $this->minProperties,
            'maxProperties' => $this->maxProperties,
            'default' => $this->default,
        ], static fn(int|float|string|bool|null $value): bool => $value !== null);
    }
}
