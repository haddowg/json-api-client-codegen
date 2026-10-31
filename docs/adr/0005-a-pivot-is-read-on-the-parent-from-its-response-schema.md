# A pivot is read on the parent, from its response schema

A pivot relation's response schema becomes a `<Type><Rel>Pivot` class read through `<rel>Pivots()`
(or `<rel>Pivot()` on a to-one) on the parent, in linkage order, while the edge builder takes only
the write schema's fields. The parent is the only place the pivot's type is known: the related
resource is the same class however it was reached.

## Considered options

A `_pivot()` accessor on the related resource reads more naturally in a loop, but a `Track` reached
through `orderedTracks` is the same class as one reached through `tracks`, so its pivot could only
be an untyped map. A standalone value class per pivot would have needed a second set of date and
enum readers in generated code; extending the runtime's `Pivot` shares the attribute readers.
