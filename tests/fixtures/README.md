# Fixtures

Copied verbatim from `json-api-ts/packages/codegen/test/fixtures/`, where they are the
reference input for the TypeScript codegen. Using the same two files here means a PHP and a
TypeScript client generated from one document can be diffed against each other, which is how
the two codegens are kept consistent.

| File | What it is |
| --- | --- |
| `music-catalog.openapi.json` | The OpenAPI 3.1 document for the music-catalog API. 56 paths, 166 component schemas, 12 resource types. |
| `music-catalog.schemas.json` | The sidecar JSON Schema map, one draft-2020-12 schema per resource type, keyed by type. |

This is the canonical codegen input. Golden-file tests generate from it and diff the committed
expected output, so treat both files as frozen: regenerating them changes every golden file at
once and makes the diff unreadable. Replace them only when the emitted document structure
itself changes, and say so in the commit.

## Known drift

The document declares pagination as flattened `page[number]` / `page[size]` query parameters.
The projector now emits the whole family as one `page` object parameter (`style: deepObject`)
instead, so this fixture is behind on that structure. The reader handles both forms, and
`tests/Descriptor/PaginatorDetectionTest.php` pins the current one against hand-built documents
rather than leaving it to a fixture that cannot exercise it. Do not "simplify" the reader to one
form until the fixture is regenerated.
