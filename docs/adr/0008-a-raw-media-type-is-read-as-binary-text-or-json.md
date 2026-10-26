---
status: accepted
---

# A raw media type is read as binary, text or JSON

Contract 10 lets a custom action answer in media types other than JSON:API, and accept a body in
several, each with its own schema. The descriptor reduces every one to a `RawMediaDescriptor` of one
of three kinds, deciding by the schema first and the media type second: `format: binary` is
binary, `application/json` or any `+json` type is JSON typed by its schema, a string schema or a
`text/*` type with no schema is text, and anything else is binary. Each kind has one PHP type on
each side of the wire, `StreamInterface` or `StreamInterface|string` for binary, `string` for text,
and the schema's shape for JSON, so the emitter never looks at a media type again. The method
signature follows the runtime's ADR 0007: `$accept` with a conditional return for a response,
`$contentType` with a conditional body for a request.

A JSON response whose schema is an object with members becomes a value class, built like a tagged
union's branch, and named after its schema component when it has one, so one component answered by
several actions is one class. A component name that would collide with a generated class falls
back to `<Type><Action><Subtype>Body`. A JSON request body is typed by its array shape and encoded
by the generated method, matching how the write forms take arrays.

## Considered options

**Reading the media type alone** misfiles a PDF declared as `application/pdf` with a string schema
and no format, and a JSON media type answered with a string. The schema is what the server
promised, so it wins.

## Consequences

A request body offered in one concrete raw media type keeps the single `StreamInterface|string`
signature it always had, whatever its schema, so an existing client does not change. A JSON:API
body offered beside raw ones generates the document input alone and warns.

The projector's default summary and description, which only name the action, are dropped by the
reader, so a document that carries them generates the docblock it always did.
