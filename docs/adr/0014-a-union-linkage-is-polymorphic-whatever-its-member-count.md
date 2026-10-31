# A union linkage is polymorphic whatever its member count

A relation is polymorphic when its linkage is an `anyOf` with no `null` branch, or names several
types, so a `MorphTo` over one type hydrates through a one-arm `match` on `type` and rejects any
other type with a `MalformedDocument`. Reading the type count instead would generate a single-type
`MorphTo` as a `BelongsTo` and rewrite every method that reads it the day a second type joins,
which is the churn core's projection was changed to prevent.

## Consequences

A polymorphic to-many has no single related type to page by, so it always takes its own paginator
from its related endpoint, else its relationship endpoint, else none.
