# Fixtures

Copied verbatim from `json-api-ts/packages/codegen/test/fixtures/`, where they are the
reference input for the TypeScript codegen. Using the same two files here means a PHP and a
TypeScript client generated from one document can be diffed against each other, which is how
the two codegens are kept consistent.

| File | What it is |
| --- | --- |
| `music-catalog.openapi.json` | The OpenAPI 3.1 document for the music-catalog API. 64 paths, 193 component schemas, 16 resource types. |
| `music-catalog.schemas.json` | The sidecar JSON Schema map, one draft-2020-12 schema per resource type, keyed by type. |

This is the canonical codegen input. Golden-file tests generate from it and diff the committed
expected output, so treat both files as frozen: regenerating them changes every golden file at
once and makes the diff unreadable. Replace them only when the emitted document structure
itself changes, and say so in the commit.

## Regenerating

Both files come from the music-catalog demo in `json-api-laravel`'s workbench, which is the app
that defines this API. The demo providers are registered in `testbench.docker.yaml` rather than
`testbench.yaml`, so the export has to load that config:

```
# in json-api-laravel
php <(sed 's/testbench.yaml/testbench.docker.yaml/' vendor/orchestra/testbench-core/testbench) \
    jsonapi:openapi:export --output=music-catalog.openapi.json
```

`build/laravel-default.json` and `build/laravel-schemas-default.json` in that repo are the same
two artifacts, and the OpenAPI export is byte-identical to the committed build file — so either
source is the canonical document.

## Both pagination wire forms are handled on purpose

This document declares pagination as one `page` object parameter (`style: deepObject`) whose
schema names the members. Documents from before `json-api` v1.0.0 flatten the same members into
one `page[number]`-style parameter each, and `json-api-ts` still has such a document committed.
The reader normalises both, and `tests/Descriptor/PaginatorDetectionTest.php` pins every
strategy against hand-built documents in both forms. Do not "simplify" it to the current form
alone.
