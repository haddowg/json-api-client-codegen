# Declared links are typed through `_declaredLinks()`

The links a resource type declares are typed through a generated `<Type>Links` returned by the
reserved `_declaredLinks()`, with a class per link object and its `meta` read the way attributes
are, while `_links()` and `_link()` keep returning every link untyped. PHP cannot narrow `_links()`'s
`array` return to an object in a subclass, and changing it everywhere would break every caller that
reads it as an array.

## Considered options

An array shape on `_links()` would type the keys but coerce nothing, so a date stays a string and a
missing required member fails as a type error at the call site rather than a `MalformedDocument`
naming the link. Merging the `allOf` that holds a type's declared links beside `ResourceLinks` into
one schema in the reader was rejected: the reader takes the named members of the non-`ResourceLinks`
entry instead, rather than deciding a general merge for every other caller.
