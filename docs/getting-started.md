# Getting started

## 1. Install

```bash
composer require haddowg/json-api-client
composer require --dev haddowg/json-api-client-codegen
```

The generated code depends on the runtime alone, so only the runtime ships to production.

## 2. Generate

Point the codegen at the document your server serves, choose a directory and a namespace:

```bash
vendor/bin/json-api-client-codegen https://music.example/docs.json generated/music-catalog \
    --namespace='App\Api\MusicCatalog'
```

The input can be a URL or a file. No API yet? Generate from the music catalog's
[document](../tests/fixtures/music-catalog.openapi.json), saved locally.

## 3. Register and commit

The output is not PSR-4: classes share one file per resource type, and a generated `autoload.php`
maps each class to its file. Register it once:

```json
{
    "autoload": {
        "files": ["generated/music-catalog/autoload.php"]
    }
}
```

Commit the directory. It is reviewed, diffed and versioned like any other code, and regenerating
rewrites the map, so a new type never needs a `composer dump-autoload`.

## 4. Use it

```php
use App\Api\MusicCatalog\MusicCatalogClient;
use haddowg\JsonApiClient\ClientOptions;

$client = new MusicCatalogClient(new ClientOptions(baseUrl: 'https://music.example'));
```

The [client's documentation](https://haddowg.github.io/json-api-client/) covers the generated
surface, from the first read onwards.

## 5. Keep it current

Regenerate whenever the API changes; output is sorted and stable, so the diff contains only what
moved. In CI, fail the build when the committed client is out of date:

```bash
vendor/bin/json-api-client-codegen openapi.json generated/music-catalog --namespace='App\Api\MusicCatalog' --check
```

??? note "In Laravel or Symfony"
    The runtime's bridges run this for every configured client: `json-api-client:generate` writes
    it, and `json-api-client:check` compares the served document with the one the committed client
    was generated from, without needing this package installed. See
    [Laravel and Symfony](https://haddowg.github.io/json-api-client/frameworks/).
