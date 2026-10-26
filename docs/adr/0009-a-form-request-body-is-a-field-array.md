---
status: accepted
---

# A form request body is a field array, not bytes

Contract 11 lets a custom action accept several request bodies, and `multipart/form-data` is the
usual one for an upload. Read as binary, as ADR 0008 would read its object schema, the caller had
to hand over a pre-encoded body, and that body could never parse: a multipart body only means
something under a `Content-Type` that names its boundary, and the generated method sends the bare
media type. So a `multipart/form-data` or `application/x-www-form-urlencoded` request body is a
fifth and sixth kind, decided by the media type alone. The generated body is
`array<string, string|int|float|bool|FilePart>` for multipart and
`array<string, string|int|float|bool>` for urlencoded, and the method hands it to the runtime's
`Request::withMultipartBody()` or `withFormBody()`, which encode it and set the media type, boundary
included. A form is the only choice of its kind that drops the `$contentType` parameter when it is
the action's single request media type, because there is nothing left to choose.

## Considered options

**Typing the fields from the schema's `properties`** would catch a misspelt field, but a form
schema describes what the server reads from the parts, and a file member has no JSON Schema type a
PHP value maps to. The field array can be narrowed later without changing the call.

## Consequences

Where a form is one choice among several, the body's static type is only known once
`$contentType` is, so the method passes it through the runtime's `FormEncoding::multipartFields()`
or `formFields()`, which check it before encoding it.
