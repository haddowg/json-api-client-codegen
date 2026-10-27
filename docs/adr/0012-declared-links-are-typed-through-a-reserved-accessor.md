---
status: accepted
---

# Declared links are typed through `_declaredLinks()`

Generator contract 14 lists the links a resource type declares as named properties of its
`<Type>ResourceLinks` (core ADR 0178), so the codegen can type them. A type that declares any gets
`_declaredLinks()` on its resource base, returning a generated `<Type>Links` with one accessor per
link, each returning null when the response leaves the link out. A link object gets its own class,
`<Type><Link>Link`, with `href()`, an accessor per static member and, when it declares `meta`
members, a `meta()` whose `<Type><Link>LinkMeta` reads each member the way an attribute is read,
so a `date-time` is a `\DateTimeImmutable`. A bare link reads as its URL. The three classes extend
the runtime's `DeclaredLinks`, `DeclaredLink` and `LinkMeta`, which share the attribute readers,
as a pivot does (ADR 0005). On an 8.4 target each accessor also gets a property hook, so
`$image->_declaredLinks()->download?->meta->expiresAt` reads the same value.

## Considered options

Typing `_links()` itself was the obvious place, and PHP doesn't allow it. `_links()` returns the
raw array on every resource and every collection, and a subclass can't narrow `array` to an
object. Changing it to an object everywhere would break every caller that reads it as an array.
So `_links()` and `_link()` stay as they were and still hold every link, declared or not.

An array shape on `_links()` would have typed the keys without coercing anything, so a date would
still be a string and a missing required member would read as a type error at the call site
rather than a `MalformedDocument` naming the link.

## Consequences

The reader takes a property as a declared link when it is an inline object schema with an `href`,
or a `$ref` to `LinkObject`. It skips `self`, a `RenderedLink`, and any link named after one of the
type's actions. That last rule is needed because a server that renders its links as strings points
an action's link at `LinkObject` too. Any other shape is an error by pointer (ADR 0001). A document
before contract 14 declares no links, so it isn't read for them at all.

A meta member a request can hide is nullable, and a static member is typed `string` with its
`const` in the docblock rather than as a literal type. Each link's description and each meta
member's description and example go into the docblocks. Attributes don't get those yet.

Adding a declared link puts it in every example of a read that renders the type, so an example
can grow past the 60 lines ADR 0011 allows. The witness's related `articles.attachments` read
lost its `@example` this way.

## Amendment: contract 18 reads them from an `allOf`

From contract 18 `<Type>ResourceLinks` is an `allOf` of the shared `ResourceLinks` and an object
holding the named links, and a type without any points at `ResourceLinks` directly (ADR 0017). The
rules above for what counts as a declared link are unchanged.
