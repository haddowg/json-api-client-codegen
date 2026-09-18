<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One field on a pivot relation member's `meta.pivot`.
 *
 * `$readOnly` and `$required` come straight from the spec's own `readOnly` flag and the pivot
 * object's `required` list. They are what a generated edge builder needs: a read-only field
 * must not appear on the write surface at all, and a required one must be enforced.
 */
final class PivotFieldDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly string $format,
        public readonly ?string $enum,
        public readonly bool $readOnly,
        public readonly bool $required,
    ) {}
}
