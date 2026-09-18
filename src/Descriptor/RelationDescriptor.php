<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One relation a resource type declares.
 *
 * `$related` and `$relationship` are independent: the first gates the related-resources read
 * (`GET /{type}/{id}/{rel}`), the second gates the linkage read and every mutation on
 * `.../relationships/{rel}`. A server can switch either off on its own, and generating against a
 * switched-off endpoint produces a 404 from code that compiled.
 *
 * `$paginator` is set only when this relation's own related endpoint paginates differently from
 * the related type's collection. Null means the two agree and the related type's kind applies.
 */
final class RelationDescriptor
{
    /**
     * @param list<string>                      $types       related JSON:API types; more than one is polymorphic
     * @param array<string, PivotFieldDescriptor> $pivotFields keyed and sorted by field name
     * @param list<RelationVerb>                $mutations   the verbs the relationship endpoint advertises
     */
    public function __construct(
        public readonly string $name,
        public readonly Cardinality $cardinality,
        public readonly array $types,
        public readonly bool $pivot,
        public readonly array $pivotFields,
        public readonly bool $related,
        public readonly bool $relationship,
        public readonly array $mutations,
        public readonly ?CountableDescriptor $countable,
        public readonly ?PaginatorKind $paginator,
    ) {}

    public function permits(RelationVerb $verb): bool
    {
        return \in_array($verb, $this->mutations, true);
    }
}
