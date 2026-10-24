---
status: accepted
---

# A single-entry `allOf` around a `$ref` is read as the `$ref`

Generator contract 9 writes a `$ref` that carries an `example`, `examples` or `default` as
`{"allOf": [{"$ref": …}], …}`, with those keywords beside the `allOf` (core ADR 0165), because OAS
3.0 tooling ignores a `$ref`'s siblings. The reader treats an `allOf` holding exactly one entry,
and that entry only a `$ref`, as the `$ref` itself, everywhere it resolves a schema:
`SpecDocument::reference()` finds it, and `refName()`, `follow()` and `dereference()` go through
it. Without this, an enum attribute with an example reads as an unknown composed value and loses
its enum type.

The wrapper's own keywords are read before the target's, so its `default` reaches the docblock
just as one beside a bare `$ref` does. An `allOf` with two entries, an entry carrying more than the
`$ref`, or an `allOf` beside `anyOf` or `oneOf` is still a composition and is merged as before.

## Considered options

Rewriting the decoded document into the bare form on load was simpler, but every pointer in an
error message would then name a node the document doesn't have.

## Consequences

The other contract 9 changes need no reader change. Every endpoint document gets its own links
component, and links are not read (ADR 0004). A to-many related read answers with
`<Type><Rel>RelatedCollection` instead of the related type's `<Type>Collection`; the codegen finds
a relation's endpoints by operationId and reads their pagination from the `page` parameter, so
neither depends on the response's component name. The music-catalog output is unchanged by the
contract; its golden diff comes from the example app (a `slug` that can't be empty, and
`publicOwner` served at `public-owner`).
