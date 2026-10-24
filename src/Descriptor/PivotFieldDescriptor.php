<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One field on a pivot relation member's `meta.pivot`.
 *
 * A relation carries two sets of these. The read set describes the `meta.pivot` a response
 * renders, where `$required` means the server always renders the field. The write set describes
 * what a relationship write may send, where `$required` means a write must send it. Before
 * contract 8 one schema served both, and `$readOnly` is how it marked a field no write can set.
 *
 * `$format`, `$enum`, `$nullable`, `$schema` and `$constraints` mean what they do on
 * {@see AttributeDescriptor}: a pivot field is an attribute that happens to live on the edge.
 */
final class PivotFieldDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly string $format,
        public readonly ?string $enum,
        public readonly bool $nullable,
        public readonly ?ValueSchema $schema,
        public readonly bool $readOnly,
        public readonly bool $required,
        public readonly Constraints $constraints = new Constraints(),
    ) {}
}
