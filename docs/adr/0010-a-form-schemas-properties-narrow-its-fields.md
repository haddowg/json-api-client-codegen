---
status: accepted
---

# A form schema's properties narrow its fields

ADR 0009 left a form body as a generic field array. When the form's schema lists `properties`, the
generated body is now an array shape of them instead: required members from `required`, the rest
optional, `string`, `int`, `int|float` and `bool` from the JSON types, a string enum as its
literals, a `format: binary` member as a `FilePart` in a multipart body, and an `array` as a `list`
of its item type, which the runtime sends as one `name[]` part per item. A urlencoded body has no
files, so a binary member there is a `string`. A member the schema types in a way PHP cannot use,
such as an object or a composition, takes any field value, and a schema without `properties` keeps
the generic array.

`additionalProperties` types the other fields. When it is `true` the shape gains
`...<string, T>` with T any field value, and when it is a schema T is that schema's field type.
When it is `false` or absent nothing is added. That does not seal the shape: PHPStan accepts a key
an array shape does not name, so a misspelt field still compiles and reaches the server, the same
blind spot ADR 0003 accepts for the write arrays. What the shape does catch is a missing required
field, a wrong value type and a value outside an enum, which is most of what a caller gets wrong.

## Considered options

**A runtime guard on unknown keys** would close the blind spot, but whether an extra form field is
an error is the server's call, and a form schema with no `additionalProperties` permits extras
under JSON Schema's own rules.

## Consequences

Narrowing a form's schema changes the body type in a regenerated client without changing the
call, so a caller passing what the server always expected keeps compiling.
