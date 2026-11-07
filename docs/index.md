# json-api-client-codegen

Reads the OpenAPI 3.1 document a JSON:API service built on
[`haddowg/json-api`](https://github.com/haddowg/json-api) serves, and writes a typed PHP client
for it. The generated code runs on [`haddowg/json-api-client`](https://haddowg.github.io/json-api-client/)
and is committed to your repository; this package is a dev dependency only, so its spec parser and
emitters never reach a production autoload map.

```
haddowg/json-api, -laravel, -symfony   (serve the OpenAPI 3.1 document)
        │
        ▼
haddowg/json-api-client-codegen        (this package: reads it, writes the typed client)
        │
        ▼
haddowg/json-api-client                (the runtime the generated client runs on)
```

## A 30-second taste

```bash
composer require --dev haddowg/json-api-client-codegen

vendor/bin/json-api-client-codegen https://music.example/docs.json generated/music-catalog \
    --namespace='App\Api\MusicCatalog'
```

```text
Generated 18 files in generated/music-catalog.
```

That is the music catalog, the API every jsonapi.rest package uses as its worked example: one file
per resource type, plus `client.php`, `enums.php` and an `autoload.php` class map. Then:

```php
$client = new MusicCatalogClient(new ClientOptions(baseUrl: 'https://music.example'));

$albums = $client->albums->query()->withArtist()->whereTitle('Abbey Road')->get();
$albums[0]->artist()?->name(); // typed: artist() exists because of withArtist()
```

!!! tip "See what it writes"
    This repository's tests generate the music catalog's client from the document the Symfony
    bundle publishes, analyse it with PHPStan at level 9, and report what each pull request
    changes in it. `composer generate-output` writes it to `build/generated-output`. The client's
    [example app](https://haddowg.github.io/json-api-client/example-app/) runs that same output
    end to end.

## Where to next

- [Getting started](getting-started.md): generate, commit, and keep the client current.
- [The CLI](cli.md): every option, and the `--check` drift gate.
- [Generated output](output.md): which files and classes, and the PHP 8.3 and 8.4 targets.
- [Reading the document](reading-the-document.md): how each part of the document becomes code.
- [Errors and warnings](errors-and-warnings.md): what is refused, what falls back, and which
  documents this version reads.
