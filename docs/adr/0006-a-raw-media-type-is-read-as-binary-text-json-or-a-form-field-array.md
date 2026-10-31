# A raw media type is read as binary, text, JSON or a form field array

Each raw action media type is reduced to one kind with one PHP type per side of the wire, decided
by the schema first and the media type second, so the emitter never inspects a media type again.
`multipart/form-data` and `application/x-www-form-urlencoded` request bodies are the exception,
decided by media type alone and taken as a field array the runtime encodes, because a pre-encoded
multipart body cannot carry the boundary its `Content-Type` must name.

## Considered options

Reading the media type alone misfiles a PDF declared as `application/pdf` with a string schema and
a JSON media type answered with a string; the schema is what the server promised. A runtime guard on
unknown form fields was rejected: whether an extra field is an error is the server's call, and a
form schema without `additionalProperties` permits extras.

## Consequences

A form schema's `properties` narrow the field array to an array shape, which catches a missing
required field or a wrong value type but not a misspelt key, since PHPStan accepts keys a shape
does not name.
