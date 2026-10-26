---
status: accepted
---

# Operation examples are carried whole and quoted when short

Contract 12 puts a worked `example` on each operation's JSON:API request body and success
responses (core ADR 0176). The descriptor reads them into an `OperationExamples` on every
`OperationDescriptor` and `ActionDescriptor`: `request` is the request body example and `responses`
maps each success status to its example, ascending. An operation with neither has `examples` null,
and the serialised descriptor leaves the key out, so a document from before contract 12 serialises
exactly as it did. A raw media type keeps its own `example` on its `RawMediaDescriptor`, because a
CSV sample is not a JSON:API document. A JSON:API example that is not an object is an error by
pointer, as ADR 0001 has it.

Each generated operation method closes its docblock with an `@example` quoting the request body
example when there is one, else the first success response's, as pretty-printed JSON indented
under the tag. When that JSON runs past 60 lines the docblock leaves it out and the descriptor
alone carries it. A collection response with its links and `meta.page` is often a hundred lines,
which buries the method it documents, while every request body in the music-catalog document fits
in 41. The docblock helper turns a `*/` inside a string value into `*\/`, which JSON reads as the
same slash, so the quoted example still parses and can't end the comment.

## Considered options

**Truncating a long example** keeps something in the docblock, but a cut-off document is neither
valid JSON nor a complete worked example, and a reader can't tell which members were dropped.

**Error responses per operation** were left out. The document shares one error response per
status across every operation, so its example is not the operation's own, and an operation already
lists its error statuses.

## Consequences

The decoded document can't tell an empty JSON object from an empty array, so an example holding
`{}` would quote as `[]`. No example in the music-catalog document or the witness holds one.
