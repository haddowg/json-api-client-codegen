# haddowg/json-api-client-codegen

Reads the OpenAPI 3.1 document published by a JSON:API service and emits a typed PHP client
for it: one class per file, PSR-4, committed to the consuming repo and regenerated when the
document changes.

The generated code depends on [`haddowg/json-api-client`](https://github.com/haddowg/json-api-client)
at runtime. This package itself never ships to production. Install it with `--dev`, run it, and
commit what it writes.

```bash
composer require --dev haddowg/json-api-client-codegen
```

It targets documents emitted by [`haddowg/json-api`](https://github.com/haddowg/json-api) and
its adapters. There is no generic fallback mode for other producers.

## Status

Not yet released, and nothing emits code yet.

What is in place is the spec reader and the descriptor it builds — the model every emitter will
read. The document is parsed through typed accessors that carry their own JSON pointer, so a
structure the codegen needs but cannot find reports
`expected components.schemas.AlbumsResource.properties.type.const` instead of failing somewhere
downstream. The descriptor built from the music-catalog fixture is committed as a golden file and
diffed on every run.

## License

MIT. See [LICENSE](LICENSE).
