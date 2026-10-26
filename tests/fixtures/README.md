# Fixtures

The music-catalog pair started as copies of `json-api-ts/packages/codegen/test/fixtures/`, the
TypeScript codegen's reference input, so a PHP and a TypeScript client generated from one
document could be diffed against each other. They have since been re-exported from the
bundle's example, most recently from the bundle's `main` on `haddowg/json-api` 1.13.1, which gives every error
code an example detail.

| File | What it is |
| --- | --- |
| `music-catalog.openapi.json` | The OpenAPI 3.1 document for the music-catalog API. 65 paths, 354 component schemas, 15 resource types, generator contract 13 (`haddowg/json-api` 1.13.1), with a worked example on 105 of its 123 operations and an example detail on every core error code. |
| `music-catalog.schemas.json` | The sidecar JSON Schema map, one draft-2020-12 schema per resource type, keyed by type. |
| `contract-witness.openapi.json` | Core's contract witness, copied verbatim from `tests/OpenApi/Fixture/contract-witness.json` on `haddowg/json-api` main at `2b57499`, after the 1.14.0 release. One server projecting every branch the projector can reach: an integer-backed enum, types named by their own singular, a type with no endpoint, from contract 10, actions answering in raw media types, from contract 12, operation examples, from contract 13, an example detail on every error code, and from contract 14, a link object and a bare link declared on `images`. Generator contract 14. Refresh it by copying the file from a newer core release. |
| `paginators.openapi.json` | Hand-built, generator contract 3, with an offset floor of 0 as core projects it. One collection per pagination strategy, each with renamed keys under `x-paginator`, a `MultiPaginator` menu on `posts` (and on its related `comments` read), an unstamped canonical `tags`, and a strategy no codegen knows on `logs`. Shaped after core's projection; not exported from a server. |

`music-catalog.openapi.json` is the canonical codegen input. Golden-file tests generate from it and diff the committed
expected output, so treat both files as frozen: regenerating them changes every golden file at
once and makes the diff unreadable. Replace them only when the emitted document structure
itself changes, and say so in the commit.

## Regenerating

Both files come from the music-catalog example in `json-api-symfony`
(`examples/music-catalog-symfony`), exported from its `default` server. Install the example
against the released core and bundle you want the document to describe, with
`symfony/expression-language` present (without it the bundle leaves the security declarations out
of the document), then boot the example's kernel and run both export commands through Symfony's
console application:

```php
use haddowg\JsonApiBundle\Examples\MusicCatalog\MusicCatalogKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

$app = new Application(new MusicCatalogKernel('dev', false));
$app->setAutoExit(false);
$app->run(new ArrayInput(['command' => 'json-api:openapi:export', '--server' => 'default', '--output' => 'music-catalog.openapi.json']));
$app->run(new ArrayInput(['command' => 'json-api:json-schema:export', '--server' => 'default', '--output' => 'schemas']));
```

The JSON Schema export writes one `<type>.json` per type into `schemas/`. Merge them into one
object keyed by type, sorted, and pretty-printed with unescaped slashes and unicode, to get
`music-catalog.schemas.json`.

The same demo in `json-api-laravel`'s workbench exports the same document; the adapters'
byte-compat check keeps the two identical.

## Both pagination wire forms are handled on purpose

This document declares pagination as one `page` object parameter (`style: deepObject`) whose
schema names the members. Documents from before `json-api` v1.0.0 flatten the same members into
one `page[number]`-style parameter each, and `json-api-ts` still has such a document committed.
The reader normalises both, and `tests/Descriptor/PaginatorDetectionTest.php` pins every
strategy against hand-built documents in both forms. Do not "simplify" it to the current form
alone.

This document is contract 13, so every page schema carries `x-paginator` and states its members'
`default` and the size cap, and its `tracks` collection is a page/cursor/offset menu: the picker
is generated from a real server's document.
The name-based fallback for unstamped documents is pinned by `PaginatorDetectionTest`'s
hand-built documents. `paginators.openapi.json` covers renamed keys and strategies a server can
configure but this API doesn't use. Its generated output is
committed under `tests/golden/paginators`, analysed by `composer phpstan-generated` together with
the page-type assertions in `tests/GeneratedTypes`, and run by `PaginationBehaviourTest`.

## The reference artefact

`artefact/` is a snapshot of `json-api-client/tests/Generated/MusicCatalog`, the hand-written
output for the `albums` type that every emitter targets. Copied at json-api-client `443fe13`, where
it was refreshed against generator contract 11.

`ArtefactParity` compares a declaration generated from `music-catalog.openapi.json` against it on
code tokens and type-bearing PHPDoc only, because the artefact's prose explains the design to a
reader and is not derivable from the document.

`artists` and `tracks` there are trimmed companions, so only `Album*` declarations and
`AlbumStatus` are compared. Re-copy it when the artefact changes upstream, and say so in the
commit.
