---
status: accepted
---

# Declared links are read from the `allOf` beside `ResourceLinks`

From contract 18 core shares one links component per context and drops the per-type and
per-relation ones (core ADR 0187). A type with declared or action links keeps a
`<Type>ResourceLinks`, now an `allOf` of the shared `ResourceLinks` and an object holding its named
links, and every other type points at `ResourceLinks` itself. The descriptor reads declared links
from the named members of that `allOf`, skipping the `ResourceLinks` entry, and still reads the
flat component contracts 14 to 17 wrote. A type pointing at `ResourceLinks` declares none.

## Considered options

Resolving the `allOf` into one merged schema in the reader would have kept `declaredLinks()`
unchanged. But `ResourceLinks` only ever adds `self`, which is skipped anyway, and a general merge
would have to decide what an `additionalProperties` beside named properties means for every other
caller.

## Consequences

Nothing else in this codegen read a links component. Pagination comes from `x-paginator`
(ADR 0002) and generated code hands `links` to the runtime untyped (ADR 0004), so the per-relation
and per-document components can go without a reader change. The witness generates the same output
it did at contract 17, apart from the document hash in `autoload.php`.

An `allOf` entry that is neither a `$ref` to `ResourceLinks` nor an inline object is an error by
pointer (ADR 0001). The `@generated` line on a link class still names
`<Type>ResourceLinks.properties.<link>`, which reads as the link's address in the component rather
than a literal JSON pointer into the `allOf`.
