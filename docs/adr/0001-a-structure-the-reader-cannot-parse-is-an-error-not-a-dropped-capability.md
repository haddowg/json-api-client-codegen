# A structure the reader cannot parse is an error, not a dropped capability

A generated client's surface is its descriptor, so a relation or endpoint silently dropped over an
unreadable shape becomes a missing method that surfaces weeks later at a call site. The codegen
therefore refuses the document with a message naming the JSON pointer it failed to read, where the
TypeScript codegen warns and continues.

## Considered options

Warning and continuing lets one unreadable relation not block the rest of the client. That matters
little here: every document comes from one projector, so an unreadable structure means the
projector grew a shape the reader has not been taught, or the document is not one of ours, and
both want attention now. Emitting a degraded shape was ruled out as a second, less-tested path.

## Consequences

A projector change that adds a shape blocks regeneration until the reader learns it, so the two
move together. Optional structures stay optional; erroring is only for what the codegen needs to
describe an endpoint at all. Pagination is the one exception (ADR 0002).
