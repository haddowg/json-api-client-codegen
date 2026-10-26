<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * A named enum component, which becomes one generated PHP enum.
 *
 * Keyed in the descriptor by its component name, which is what an
 * {@see AttributeDescriptor::$enum} points at.
 */
final class EnumDescriptor
{
    /** @param list<EnumCase> $cases in document order */
    public function __construct(
        public readonly string $name,
        public readonly array $cases,
        public readonly ?string $description,
    ) {}

    /** Whether the enum is integer-backed. The reader admits only all-integer or all-string cases. */
    public function intBacked(): bool
    {
        return $this->cases !== [] && \is_int($this->cases[0]->value);
    }
}
