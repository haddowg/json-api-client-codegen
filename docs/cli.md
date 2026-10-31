# The CLI

```text
json-api-client-codegen <openapi-document> <output-directory> --namespace=<Namespace> [options]
```

| Argument or option | Meaning |
|---|---|
| `<openapi-document>` | the document, as a file path or a URL |
| `<output-directory>` | where the client is written; created when missing |
| `--namespace=<Namespace>` | the namespace of every generated class (required) |
| `--client=<Name>` | the root client's name prefix; defaults to the namespace's last segment, so `App\Api\Music` gives `MusicClient` |
| `--target=8.3\|8.4` | the PHP version to generate for; defaults to the running PHP |
| `--check` | write nothing, and exit 1 when the output directory is out of date |
| `--help` | show the usage |

## Exit codes

| Code | Meaning |
|---|---|
| `0` | generated, or `--check` found the client up to date |
| `1` | the document was refused, or `--check` found files that differ |
| `2` | the arguments were wrong |

## Writing

Every file is rewritten, and any file the codegen wrote before but no longer generates is deleted.
A file it did not write, one without `@generated` in it, is left alone, so the directory can hold
your own files beside the client.

## Checking

`--check` generates in memory and compares with the directory, listing each file that differs or
should not be there. It catches a changed document, a hand edit to the generated code, and a codegen
upgrade that changes the output.

```text
The generated client is out of date. Regenerate it; these files differ:
  resource-albums.php
```

Warnings go to standard error as `Warning: …`; see [errors and warnings](errors-and-warnings.md).
