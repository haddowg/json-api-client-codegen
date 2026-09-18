<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One relation a resource type declares.
 *
 * The related read and the relationship read are independent, which is why they are two separate
 * members rather than one flag: a server can switch off `GET /{type}/{id}/{rel}` while leaving
 * the linkage endpoint exposed, or the reverse, and generating against a switched-off endpoint
 * produces a 404 from code that compiled. Either being null IS the suppression signal.
 *
 * Each endpoint is a full {@see OperationDescriptor} rather than a boolean because the statuses
 * genuinely differ per verb — a linkage read declares six in the music-catalog document and a
 * linkage mutation nine — so a generated method's `@throws` list has to come from its own
 * endpoint, not from a union across the type.
 *
 * `$paginator` is set only when this relation's own related endpoint paginates differently from
 * the related type's collection. Null means the two agree and the related type's kind applies.
 */
final class RelationDescriptor
{
    /**
     * @param list<string>                        $types       related JSON:API types; more than one is polymorphic
     * @param array<string, PivotFieldDescriptor> $pivotFields keyed and sorted by field name
     * @param array<string, OperationDescriptor>  $mutations   keyed by {@see RelationVerb} value
     */
    public function __construct(
        public readonly string $name,
        public readonly Cardinality $cardinality,
        public readonly array $types,
        public readonly bool $pivot,
        public readonly array $pivotFields,
        public readonly ?OperationDescriptor $relatedRead,
        public readonly ?OperationDescriptor $relationshipRead,
        public readonly array $mutations,
        public readonly ?CountableDescriptor $countable,
        public readonly ?PaginatorDescriptor $paginator,
    ) {}

    /** False when the related-resources read is suppressed (`withoutRelatedEndpoint()`). */
    public function related(): bool
    {
        return $this->relatedRead !== null;
    }

    /** False when the linkage read is suppressed (`withoutRelationshipEndpoint()`). */
    public function relationship(): bool
    {
        return $this->relationshipRead !== null;
    }

    public function permits(RelationVerb $verb): bool
    {
        return isset($this->mutations[$verb->value]);
    }

    public function mutation(RelationVerb $verb): ?OperationDescriptor
    {
        return $this->mutations[$verb->value] ?? null;
    }

    /** @return list<RelationVerb> */
    public function verbs(): array
    {
        return \array_values(\array_filter(
            RelationVerb::cases(),
            fn(RelationVerb $verb): bool => $this->permits($verb),
        ));
    }
}
