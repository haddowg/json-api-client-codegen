---
status: accepted
---

# A union linkage is polymorphic whatever its member count

The descriptor used to treat a relation as polymorphic when its linkage named more than one type.
From contract 16 core projects a `MorphTo` or `MorphToMany` over one type as a one-member `anyOf`
of identifier refs, and its related document as a one-member `anyOf` of resource refs (core ADR
0184), so the count no longer says what the server declared. `RelationDescriptor::$polymorphic` is
now read from the linkage's shape: an `anyOf` with no `null` branch is a union, and so is any
linkage naming several types. The `anyOf` that only makes a to-one nullable is not, so a nullable
`BelongsTo` stays a plain relation.

A polymorphic relation hydrates each related resource through a `match` on its `type`, which
refuses a type the relation does not declare. That is what a multi-type relation already generated.
A single-type one used to call the related class's `_hydrate()` directly, and now takes the same
`match` with one arm, in the related reads, their page collectors and the include accessors. Its
signatures don't change: `related()` still returns `?People` for a `MorphTo` over `people`, and its
write inputs are still `Identifier|People|null`.

## Considered options

**Keeping the type count** would generate a single-type `MorphTo` exactly as a `BelongsTo`, and a
second type joining later would then rewrite every method that reads it. Core changed the
projection to stop that churn, so the descriptor follows it.

## Consequences

A client regenerated from a contract-16 document whose `MorphTo` has one type rejects a related
resource of any other type with a `MalformedDocument`, where it used to hydrate it into the one
class. An older document collapses such a relation to a plain `$ref`, so it generates as before.

The serialised descriptor writes `polymorphic` only when it is true, so the music-catalog descriptor
gains it on its two multi-type relations and nothing else moves.

A polymorphic to-many relation whose related endpoint advertises no `page` parameter still reads
into its first type's page class, as a multi-type relation always has. From contract 16 core leaves
the related type's `page` off a single-type polymorphic endpoint too, so neither shape is paged by
what the document says. That is unchanged here and left for its own change.
