<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The response projection one operation accepts: the `include` paths and `fields[TYPE]`
 * members it declares.
 *
 * Present only on an operation that declares `include` or a `fields[…]` parameter, so a
 * projection argument is generated exactly where the contract permits one. A write whose
 * success response carries no resource document (a relationship mutation, a `202` job) declares
 * neither and gets none.
 */
final class ProjectionDescriptor
{
    /**
     * @param list<string>                $includable include paths, in document order
     * @param array<string, list<string>> $fieldsets  member tokens keyed and sorted by type
     * @param string|null                 $relatedQuery the Relationship Queries profile, when the
     *                                                  operation declares `relatedQuery`
     */
    public function __construct(
        public readonly array $includable,
        public readonly array $fieldsets,
        public readonly ?string $relatedQuery = null,
    ) {}
}
