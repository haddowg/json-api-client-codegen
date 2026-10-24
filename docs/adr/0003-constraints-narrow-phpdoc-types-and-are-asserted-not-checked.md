---
status: accepted
---

# Constraints narrow PHPDoc types, and the generated code asserts them rather than checking them

Generator contract 5 projects a validation constraint into a schema only when every value that
schema describes satisfies it, so the read, create and update schemas of one attribute each carry
their own keywords. The codegen reads them per schema and narrows the generated PHPDoc type where
PHPStan can state the constraint exactly: integer bounds become `int<min, max>` (`positive-int`
for a floor of 1), a `minLength` of at least 1 becomes `non-empty-string`, and a `minItems` of at
least 1 becomes `non-empty-list`. The native type never changes. Everything else (`pattern`,
`format`, `maxLength`, `multipleOf`, `uniqueItems`, a `number`'s bounds) becomes one sentence in
the accessor's or setter's docblock, and a page member's `default` is stated the same way.

On a read, the narrowed type is asserted with an inline `@var` once the JSON type is known, not
checked. The server enforces the constraint on every write that can set the value, so a client
that rejected a response over it would be second-guessing the only party that knows. On a write,
the narrowed type is a static contract only: passing `0` as a `positive-int` is a PHPStan error,
and nothing clamps or rejects it at runtime, so the server's `422` stays the authority.

## Considered options

Checking the bound at runtime, the way the read guards check JSON types, was rejected. A guard
that dropped or threw on an out-of-range value would turn a server's legitimate data into a client
failure the moment the server's rules and the document drifted, and the type it protects is one
the server already guarantees.

`non-negative-int` reads well but the paging parameters have always been typed `int<0, max>`, so
a floor of 0 stays in that form and only `positive-int` gets the alias.

## Consequences

The narrowing is as good as the document. Before contract 5 a read schema took the update
context's keywords, which a create could have broken, so an older document's read constraints are
not read at all and its reads stay unnarrowed. Its write schemas always held for their own write,
so writes and page members narrow whatever the contract.

Members of nested objects and tagged-union branches are narrowed but get no prose sentence: an
array shape has nowhere to put one.
