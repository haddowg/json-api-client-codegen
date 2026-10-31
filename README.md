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

## Usage

```bash
vendor/bin/json-api-client-codegen openapi.json generated/music-catalog --namespace='App\Api\MusicCatalog'
```

That writes one file per resource type, plus `enums.php`, `client.php` and an `autoload.php`
class map to register once through composer.json's `autoload.files`. Commit the output.
`--check` writes nothing and exits 1 when the committed output no longer matches the document,
which is the drift gate for CI. `--client` names the root client (default: the last namespace
segment) and `--target` the PHP version (`8.3` or `8.4`, default: the running one).

In a Laravel or Symfony application, the runtime's `json-api-client:generate` command runs this
for each configured client.

The document is read through typed accessors that carry their own JSON pointer, so a structure the
codegen needs but cannot find reports `expected components.schemas.AlbumsResource.properties.type.const`
instead of failing somewhere downstream. The documentation is at
[haddowg.github.io/json-api-client-codegen](https://haddowg.github.io/json-api-client-codegen/).

## Status

Not yet released; nothing is on Packagist yet.

## License

MIT. See [LICENSE](LICENSE).
