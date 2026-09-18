---
status: accepted
---

# A structure the reader cannot parse is an error, not a dropped capability

The TypeScript codegen warns and continues when it meets a relationship whose linkage shape it
does not recognise, or an endpoint whose `page[…]` parameters match no paginator it knows. The
warning goes to stderr and generation succeeds. We error instead, with a message naming the JSON
pointer that was being read.

The reason is what this codegen produces. A generated client's surface *is* the descriptor: an
absent capability is an absent method, so a relation silently dropped from the descriptor is a
relation the caller cannot reach, and the failure appears at the call site as "undefined method"
weeks later. There is no runtime fallback to catch it, because the whole design rests on not
having one. Dropping the relation converts a loud spec problem into a quiet API problem.

The document is read through a typed node that carries its own pointer, so this costs nothing to
implement: `expected components.schemas.AlbumsArtistRelationship.properties.data to declare
linkage as a $ref, an allOf carrying one, or an anyOf of them` falls out of the read that failed,
rather than being a message someone had to remember to write.

## Considered options

Warning and continuing is what the TypeScript codegen does, and it has one real advantage: a
single unreadable relation does not block generating the other ninety per cent. In practice that
advantage is small here. The documents this reads come from one projector, so an unreadable
structure means either the projector grew a shape the codegen has not been taught or the document
is not one of ours — and both want attention now, not a line of stderr scrolled past during a
build.

Emitting the relation with a degraded shape was never on the table. It is the generic mode that
CONTEXT.md rules out, on the same grounds: a second, permanently less-tested path.

## Consequences

When the projector grows a new linkage shape, codegen stops until the reader is taught it. That
is the intended trade — the alternative is a client quietly missing the new relation — but it
does mean a projector change can block a consumer's regeneration, so the reader and the projector
move together.

Two failure modes stay outside this. A document *older* than the codegen expects already errors
here, because the structure the reader wants is simply absent. A document *newer* does not: the
new structure goes unread and nothing in the document says so, which is what
`info.x-generator.contract` exists to catch. That field is absent from every document emitted so
far, and its absence is treated as no signal rather than a failure.

Optional structures are read with the optional accessors and stay optional. Erroring is for
structures the codegen must have in order to describe the endpoint at all, not for every member
the projector happens not to emit.
