<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * A static member of a declared link object: `rel`, `title`, `type`, `hreflang` or
 * `describedby`. Each is a string, and every one but `describedby` states its one value as a
 * `const`.
 */
final class LinkMemberDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $const,
        public readonly bool $required,
    ) {}
}
