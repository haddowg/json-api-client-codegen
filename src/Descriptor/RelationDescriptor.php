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
 * A polymorphic to-many always sets it, to none when its endpoints advertise no `page`, because
 * no single related type's collection speaks for the union.
 *
 * The related and relationship reads carry their own paths, which from contract 8 use the
 * relation's URI field name and so may differ from `$name` (core ADR 0161).
 *
 * `$polymorphic` says the relation's linkage is a union of identifiers, which is how a `MorphTo`
 * or `MorphToMany` is projected. From contract 16 that holds even with one member, so it is read
 * from the linkage's shape rather than inferred from how many types it names.
 */
final class RelationDescriptor
{
    /**
     * @param list<string>                        $types            related JSON:API types
     * @param array<string, PivotFieldDescriptor> $pivotFields      the `meta.pivot` a read renders, keyed and sorted by field name
     * @param array<string, OperationDescriptor>  $mutations        keyed by {@see RelationVerb} value
     * @param array<string, PivotFieldDescriptor> $writePivotFields the `meta.pivot` a write may send, keyed and sorted by field name
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
        public readonly array $writePivotFields = [],
        public readonly bool $polymorphic = false,
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
