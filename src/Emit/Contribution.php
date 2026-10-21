<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * Doc tags and members one emitter adds to a declaration another emitter owns.
 *
 * `@phpstan-type` aliases are class-local, and the generated code keeps every alias and
 * descriptor constant for a type on its concrete class (`Album`), though several emitters own
 * them: the read model its attribute shapes, the write emitters the input shapes, the query
 * emitter the filter and token shapes. Each contributes here, and the {@see Assembler} merges
 * contributions into the owner in `$order`, so the result is the same whichever emitter runs first.
 *
 * `$tags` are whole docblock tag lines (`@phpstan-type AlbumRangeShape array{…}`), placed above
 * the owner's `@generated` line. `$members` is class-body source (constants, typically, with
 * their docblocks), placed at the top of the owner's body.
 */
final class Contribution
{
    /**
     * @param list<string> $tags
     * @param list<string> $imports fully-qualified names `$members` refers to by short name
     */
    public function __construct(
        public readonly string $target,
        public readonly int $order,
        public readonly array $tags = [],
        public readonly string $members = '',
        public readonly array $imports = [],
    ) {}
}
