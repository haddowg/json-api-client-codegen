# haddowg/json-api-client-codegen

The dev-only CLI that reads an OpenAPI document emitted by `haddowg/json-api` and writes a typed PHP
client for it. Core concepts (resource, relation, paginator, declared link, error catalogue…) are
defined in [haddowg/json-api's CONTEXT.md](https://github.com/haddowg/json-api/blob/main/CONTEXT.md),
and the generated client's own terms in
[haddowg/json-api-client's CONTEXT.md](https://github.com/haddowg/json-api-client/blob/main/CONTEXT.md).

## Language

### Reading the document

**Spec document**:
The OpenAPI document as the codegen reads it: a reader for the structure `haddowg/json-api` emits,
not a general OpenAPI parser.
_Avoid_: spec, schema (a schema is one entry under `components.schemas`)

**Node**:
One value inside the decoded spec document, carrying the JSON pointer it was reached by.

**Generator contract**:
The integer the projector stamps as `info.x-generator.contract`, naming the structure the document
was emitted with.
_Avoid_: contract version, schema version

**Supported range**:
The span of generator contracts the codegen reads, from `ContractCompatibility::MINIMUM` to
`MAXIMUM`; below it is an error, above it a warning.

**API descriptor**:
The in-memory model of one service, built from the spec document and read by every emitter.
_Avoid_: descriptor alone (the runtime uses that word for the generated per-type map), model

**Warning**:
Something the codegen read around rather than generated, such as an unreadable paginator,
reported through `Generator::warnings()`.

**Raw media kind**:
What a non-JSON:API action media type reduces to: binary, text, JSON, multipart or urlencoded form.

**Preferred method**:
The one method of a multi-method action that keeps the action's name: POST where advertised,
otherwise the first of GET, PUT, PATCH and DELETE.

**Action alias**:
The key a multi-method action's other methods are generated under, the action's name joined with
the method (`exportGet`).

### Emitting code

**Emitter**:
One artefact family (read model, writes, reads, surface, enums, root) that turns the API descriptor
into the declarations it owns.

**Class unit**:
One generated declaration, without its `<?php`, namespace or `use` lines, as an emitter hands it to
the assembler.

**Contribution**:
Doc tags and members one emitter adds to a declaration another emitter owns.

**Assembler**:
What groups class units into their files, orders them deterministically and writes the class map.

**File key**:
The generated file a declaration belongs in: `resource-<type>.php` per resource type, or a shared
`enums.php` or `client.php`.

**Class map**:
The generated `autoload.php` the consumer registers through `autoload.files`.

**PHP target**:
The PHP version generated code is written for, 8.3 or 8.4; an 8.4 target adds property hooks that
delegate to the method accessors.

**Error shape**:
A catalogue component (`Error403InsufficientScope`) grouping the codes at one status that fill the
same `source` member and declare the same `meta`, each code a `const` branch of its `code`. The
codegen emits a class per code, and a value class for a declared `meta`.

**Scoped code**:
A code only some operations raise, offered through an error document narrowed to those operations
(`ErrorDocument409WithArticleLocked`); its class names the operations.

### Testing

**Generated output**:
The client the test suite generates from its documents, never committed: analysed at level 9,
run by the behaviour tests, and diffed between a pull request's base and head for review.
_Avoid_: golden file, snapshot

**Generated-output report**:
The pull request comment showing how a change moves the generated output, which fails CI until
the `generated-output` label acknowledges it.

**Pinned document**:
A server document the tests read from an exact published package version in `package.json`,
rather than from a committed copy.

**Music-catalog fixture**:
The reference spec document, the bundle's music-catalog example as `@haddowg/json-api-symfony-specs`
publishes it.

**Contract witness**:
Core's one-server document projecting every branch the projector can reach, as
`@haddowg/json-api-witness` publishes it.
