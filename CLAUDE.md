# CLAUDE.md — executor playbook (json-api-client-codegen)

`haddowg/json-api-client-codegen` is the **dev-only CLI** that reads a JSON:API service's
OpenAPI 3.1 document and emits a typed PHP client for it. The generated code depends on
[`haddowg/json-api-client`](https://github.com/haddowg/json-api-client) at runtime; this package
is never installed in production, which is why it is a separate repo rather than a namespace
inside the runtime. It mirrors the split of `@haddowg/json-api-client` and
`@haddowg/json-api-codegen` on the TypeScript side.

## Where the design lives

`../json-api-client/CONTEXT.md` is the authority on **what** gets generated and why: every
resolved decision about the emitted surface, the glossary, the server prerequisites, and the
`json-api-ts` consistency list. Its ADRs carry the rationale for the hard-to-reverse ones.

Read it before changing an emitter. Several of its decisions rule out designs that look
obviously correct, and each was verified against PHPStan level 9 rather than reasoned about.

## Gates (all green before anything lands)

```bash
composer test                            # PHPUnit, including golden-file emitter tests
vendor/bin/phpstan --memory-limit=1G     # PHPStan level 9
composer cs-check                        # PHP-CS-Fixer, PER-CS 2.0
```

CI enforces all three across PHP 8.3 / 8.4 / 8.5 x lowest/highest.

**Emitter changes are proven by golden files, not by unit assertions on strings.** The
music-catalog fixture is the reference input; its expected output is committed and diffed. A
golden file that changes is a reviewable diff, which is the whole point. Regenerate
deliberately, never to make a test pass.

The generated output must itself pass PHPStan level 9. An emitter whose output only type-checks
by accident is a broken emitter.

## Conventions

- **Namespace** `haddowg\JsonApiCodegen\`; PHP `^8.3`. Binary at `bin/json-api-client-codegen`.
- **Conventional Commits** for every commit and PR title. PRs are squash-merged and
  release-please drives versioning, so a non-conforming title breaks the release. PR
  descriptions read as external-contributor prose. Follow `~/.claude/references/commits.md` and
  `~/.claude/references/pull-requests.md`. Rebase with `--force-with-lease`.
- Record architecture decisions as ADRs under `docs/adr/`.

## What this codegen assumes

**It targets documents emitted by `haddowg/json-api`.** There is deliberately no detection
layer, no structural fingerprinting, and no degraded generic mode: a second, permanently
less-tested code path was judged not worth it for a nice-to-have.

The document is read through a **typed reader**, never raw array access, so a missing structure
surfaces as `expected components.schemas.AlbumsResource.properties.type.const` rather than a
`TypeError` deep in an emitter. That is a consequence of how the reader is written, not a
detection feature, and it is the only reason a non-haddowg document fails legibly.

**Version compatibility** is a single monotonic integer, `info.x-generator.contract`, bumped by
the projector whenever the emitted structure changes. This codegen declares a supported
`[min, max]`: above `max` it warns that newer capabilities exist and were not generated, below
`min` it errors. The dangerous failure is silent under-generation against a newer server, and
this is the only thing that catches it. A feature-token list was rejected: it names what
changed, at the cost of two hand-maintained lists that must agree.

The supported range is `[1, 7]`. Contract 2 (json-api 1.1.0) added the error catalogue, shared
error responses and write-side `include`/`fields`; the descriptor reads the first into `errors`,
and the last into each operation's `projection`. Contract 3 added `x-paginator` on page schemas,
which the descriptor reads into each `PaginatorDescriptor`: the strategy, its role-to-key map, and
for a menu its arms and default (ADR 0002). Contract 4 (json-api 1.3.0) gave each error code a
`description` and sometimes an example `detail`; both are read into its `ErrorCodeDescriptor` and
written into the docblock of any code class this codegen emits. Contract 5 (json-api 1.5.0)
projects each constraint only into the schemas it holds for, admits `null` on a read attribute
unless the server guarantees a value, puts a `default` and a size cap on page members, and drops
write-only relations from the read schemas. The descriptor reads the constraints of every
attribute, value schema and page member, and the write-only relations from the write documents;
the emitters narrow PHPDoc types from them (ADR 0003). Contract 6 (json-api 1.5.1) lists the
members a nested read object always renders in its `required`, which the nested shapes already
honoured, so nested read members lose their `?`. Contract 7 (json-api 1.6.0) renders built links as
link objects described per context; generated code passes `links` to the runtime, which reads
`href` from either form, so no links component is read, and an action body naming a type the
document does not describe is an error by pointer (ADR 0004). Raise `MAXIMUM` only in the change that
teaches the descriptor to read whatever the next contract adds.

**Pagination warns rather than errors.** An unknown strategy, an unstamped member set outside the
canonical names, or an unstamped menu generates the endpoint unpaginated and reports a warning
through `Generator::warnings()`. This is the one deliberate exception to "unreadable is an error"
(ADR 0001), and it matches the TypeScript codegen.

## Emitter discipline

- **Output is one file per resource type plus a generated class map, committed to the consuming
  repo.** Files are named `resource-<type>.php` (a hyphen no class name can contain, so nothing
  collides on a case-insensitive filesystem), alongside `enums.php`, `client.php` and the
  `autoload.php` map the consumer registers through `autoload.files`. The 1.1.0 music-catalog
  document's default server is about 170 classes in about 19 files. Emission is sorted and stable
  so a regeneration diff contains only genuinely changed types. See ADR 0004 in the runtime repo,
  and its `tests/Generated/MusicCatalog` artefact for the exact shape.
- **Core's error codes are not emitted.** The runtime ships a class per code in core's catalogue
  (runtime ADR 0005). Emit a code class only for a code in the descriptor's `errors` that the
  installed runtime's `ErrorCodes::MAP` lacks.
- **Generate exactly what the contract permits.** An absent capability is an absent method, not
  a runtime guard. This is the rule the whole design rests on, and it means an emitter bug
  produces a wrong API surface rather than a wrong value.
- **Collision detection is required** where generated names meet user-chosen ones: the
  `create`/`update` static factories against attributes of those names, and filter methods that
  normalise to the same name (`artist.name` and `artistName` both give `whereArtistName`). Warn
  at build time and route the colliding member through a fallback.
- Reserved accessors take a **leading underscore**, which JSON:API guarantees can never begin a
  member name. See ADR 0002 in the runtime repo.

## Working with the siblings

`../json-api-client` is where the generated code's runtime lives, so a change to an emitted
shape lands in both repos together. `../json-api` and its adapters emit the documents this
reads; a structure this codegen needs but cannot find is usually a projector gap, and
`CONTEXT.md` lists the outstanding ones. `../json-api-ts` is the other codegen: its descriptor
model is worth reading before inventing a new one, and any divergence is either deliberate and
recorded or an issue to raise there.
