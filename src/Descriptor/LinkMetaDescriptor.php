<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One member of a declared link's `meta`, projected the way an attribute is on a read.
 *
 * `$format`, `$enum`, `$nullable`, `$schema` and `$constraints` mean what they do on
 * {@see AttributeDescriptor}. `$required` means a request cannot hide the member, so the server
 * always renders it. `$example` is the member's example, `null` when it declares none.
 */
final class LinkMetaDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly string $format,
        public readonly ?string $enum,
        public readonly bool $nullable,
        public readonly ?ValueSchema $schema,
        public readonly bool $required,
        public readonly Constraints $constraints = new Constraints(),
        public readonly ?string $description = null,
        public readonly mixed $example = null,
    ) {}
}
