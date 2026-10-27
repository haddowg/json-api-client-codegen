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

A polymorphic to-many has no one related type whose collection could decide how it pages, so its
reads follow its own endpoints. The descriptor always gives such a relation its own paginator: the
one its related endpoint advertises, or failing that its relationship endpoint, and none when
neither advertises `page`. It keeps that paginator even when it matches a member type's, which a
monomorphic relation would defer to. With a `page` its `get()` and `related()` return a
`PaginatedCollection` of the member union in that page class, as `libraries.items` does in the
music catalog. Without one they return a `ResourceCollection` of it, as the witness's `gallery`
and `attachments` do, where both used to read into the `images` page class because `images` was
the first type and pages. A monomorphic relation is decided as before.

The element type was already the union of the member classes in every relation read, sorted, so
`attachments` reads `Image|Video`. The include accessors read a hydrated to-many as a
`ResourceCollection` of the union and never page, so they needed no change.
