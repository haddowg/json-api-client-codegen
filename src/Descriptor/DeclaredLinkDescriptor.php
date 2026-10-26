<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One link a resource type declares, read from its `<Type>ResourceLinks` (generator contract 14).
 *
 * A link object (`$object`) renders as `{href, …}` on every response and carries its static
 * members and its `meta`. A bare link is the open `LinkObject` union, read as its URL. Every
 * declared link is optional on the wire, because the server leaves it out when its href resolves
 * to nothing.
 */
final class DeclaredLinkDescriptor
{
    /**
     * @param array<string, LinkMemberDescriptor> $members the static members, `href` and `meta` aside, in document order
     * @param array<string, LinkMetaDescriptor>   $meta    the `meta` members, in document order
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $object,
        public readonly ?string $description = null,
        public readonly array $members = [],
        public readonly array $meta = [],
    ) {}
}
