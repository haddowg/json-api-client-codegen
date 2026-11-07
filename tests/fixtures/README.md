# Fixtures

The documents the suite generates from. Two kinds:

- **Server documents** are pinned, not committed. `package.json` pins the packages the servers
  publish, at exact versions, and `npm install` puts them in `node_modules`, where
  `Tests\Support\Fixtures` reads them:

  | Package | File read | What it is |
  | --- | --- | --- |
  | `@haddowg/json-api-symfony-specs` | `music-catalog.openapi.json` | The bundle's music-catalog example, its `default` server: the canonical codegen input. 15 resource types, worked examples on most operations, the error catalogue grouped into shapes. |
  | `@haddowg/json-api-witness` | `contract-witness.json` | Core's contract witness: one server projecting every branch the projector can reach (raw and multi-body actions, declared links, discriminated and undiscriminated unions, sort families, deprecations, closed objects, scoped error codes). |

- **Hand-built documents** describe what no server exports, so they stay here:

  | File | What it is |
  | --- | --- |
  | `paginators.openapi.json` | Hand-built, with an offset floor of 0 as core projects it. One collection per pagination strategy, each with renamed keys under `x-paginator`, a `MultiPaginator` menu on `posts` (and on its related `comments` read), an unstamped canonical `tags`, and a strategy no codegen knows on `logs`. Shaped after core's projection; not exported from a server. |

## Installing the pinned documents

GitHub Packages needs a token even to read, so map the scope (the committed `.npmrc` does) and
give npm a token with `read:packages`, then install:

```bash
export NODE_AUTH_TOKEN="$(gh auth token)"   # after `gh auth refresh -s read:packages`, once
export NPM_CONFIG_USERCONFIG="$(.github/scripts/npm-auth)"
npm install
```

Without registry access, export the documents from sibling checkouts into the same place instead:

```bash
php ../json-api/bin/export-contract-witness.php node_modules/@haddowg/json-api-witness
php ../json-api-symfony/bin/export-specs.php node_modules/@haddowg/json-api-symfony-specs
```

## Moving a pin

Pin a release (`X.Y.Z`). To test against a server change before it is released, pin the
`0.0.0-pr.<number>.<sha>` its pull request's `prerelease` job comments; `.github/scripts/check-pins`
warns about it on a pull request and fails it on `main` and on a release pull request, so pin
the release before merging. The generated-output report shows what a pin change does to the
output, beside any emitter change in the same pull request.

## The generated output

Nothing commits it. `composer generate-output` writes every suite of
`Tests\Support\GeneratedOutput` to `build/generated-output` (and the behaviour tests write the
suite they run), from these documents. See ADR 0015 for how a change to it is reviewed.

## Both pagination wire forms are handled on purpose

This document declares pagination as one `page` object parameter (`style: deepObject`) whose
schema names the members. Documents from before `json-api` v1.0.0 flatten the same members into
one `page[number]`-style parameter each, and `json-api-ts` still has such a document committed.
The reader normalises both, and `tests/Descriptor/PaginatorDetectionTest.php` pins every
strategy against hand-built documents in both forms. Do not "simplify" it to the current form
alone.

Every page schema in this document carries `x-paginator` and states its members'
`default` and the size cap, and its `tracks` collection is a page/cursor/offset menu: the picker
is generated from a real server's document.
The name-based fallback for unstamped documents is pinned by `PaginatorDetectionTest`'s
hand-built documents. `paginators.openapi.json` covers renamed keys and strategies a server can
configure but this API doesn't use. Its generated output, the `paginators` suite, is analysed by
`composer phpstan-generated` together with the page-type assertions in `tests/GeneratedTypes`,
and run by `PaginationBehaviourTest`.

## The reference artefact

`artefact/` is a snapshot of `json-api-client/tests/Generated/MusicCatalog`, the hand-written
output for the `albums` type that every emitter targets, together with the generated `errors.php`
its client hands to the runtime. Copied from json-api-client in the change that generates every
error code.

`ArtefactParity` compares a declaration generated from the music catalog against it on
code tokens and type-bearing PHPDoc only, because the artefact's prose explains the design to a
reader and is not derivable from the document.

`artists` and `tracks` there are trimmed companions, so only `Album*` declarations and
`AlbumStatus` are compared. Re-copy it when the artefact changes upstream, and say so in the
commit.
