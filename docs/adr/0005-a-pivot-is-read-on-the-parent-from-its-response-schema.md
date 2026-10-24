---
status: accepted
---

# A pivot is read on the parent, from its response schema

Generator contract 8 gives a pivot relation two schemas: `<Type><Rel>Pivot` for the `meta.pivot`
a response renders, and `<Type><Rel>WritePivot` for what a write may send (core ADR 0160). The
codegen reads each into its own set of pivot fields. The edge builder takes the write set, so a
field no write can set is not on it. The read set becomes a `<Type><Rel>Pivot` class extending the
runtime's `Pivot`, with an accessor per field, and the resource base gets `<rel>Pivots()` (or
`<rel>Pivot()` on a to-one), which wraps each linkage member's pivot in linkage order. A field in
the response schema's `required` reads strictly. Any other field reads as nullable, because a
pivot takes no sparse fieldset and a field the response leaves out is just absent.

## Considered options

A `_pivot()` accessor on the related resource, which the runtime's CONTEXT.md once sketched as
`$track->_pivot()->position`, reads more naturally inside a loop over `$playlist->orderedTracks`.
It can't be typed. A `Track` hydrated through `orderedTracks` is the same class as one hydrated
through `tracks` or fetched by id, so its pivot accessor could only return an untyped map. The
pivot rides on the parent's linkage, so the parent is where its type is known, and reading it
there doesn't need the relation included.

A constructor-and-`_from()` value class, like a tagged union's branch, was the other way to type
the fields. It would have meant a second set of readers for dates and enums in generated code.
The runtime's `Pivot` shares the readers an attribute uses, so a pivot field and an attribute of
the same schema are read by the same body.

## Consequences

An older document shares one pivot schema between both directions and flags a field no write can
set `readOnly`. The codegen drops those from the write set either way, so its edge is unchanged.
Its read schema lists only the fields a write must send in `required`, so most of its pivot
fields read as nullable.

Contract 8 also keys a relation's related and relationship paths by its URI field name, which
need not be the relation's name (core ADR 0161). The codegen finds each endpoint by its
operationId, `fetchRelated.<type>.<relation>` and so on, which keeps the relation's name. A
document without operationIds is read by name, unless the path there belongs to another
relation.

A relation whose write bodies mark it `writeOnly` (core ADR 0162) is a write-only relation even
if a read schema still shows it. An older document is read as before, from the relation's
absence from the read schema.
