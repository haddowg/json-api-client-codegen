# Generated output

One file per resource type, plus three shared files:

| File | Holds |
|---|---|
| `resource-<type>.php` | everything the type generates (below) |
| `enums.php` | every enum the document declares |
| `client.php` | the root client: one property per type, and `atomic()` when the server offers it |
| `autoload.php` | the class map, and the sha256 of the document it was generated from |

File names carry a hyphen, which no class name can, so a file never shadows a class on a
case-insensitive filesystem. Within a file, declarations are ordered interfaces, traits, enums,
abstract bases, then final classes, and the output is sorted so a regeneration diff holds only what
changed. When the API adds error codes beyond the runtime's own, a generated error-code map and one
class per code are added too.

## One type's classes

For `albums` in the music catalog ([the whole file](../tests/golden/generated/resource-albums.php)):

| Class | What it is | Generated when |
|---|---|---|
| `AlbumHasArtist`, `AlbumHasTracks` | relation markers an include narrows to | per relation a read can include |
| `AlbumBase`, `Album` | the resource class every read returns | always |
| `AlbumProjecting` | the shared `withX()` and `fields()` | a read projects |
| `AlbumQuery` | the collection read | the type has a collection read |
| `AlbumProjection` | a write's or a read's `include` and `fields` | an operation returning the type projects |
| `AlbumReadProjection` | the same, plus Relationship Query closures | a single read declares `relatedQuery` |
| `AlbumCreate`, `AlbumCreateBuilder` | the create input | the type can be created |
| `AlbumUpdate` | the update input | the type can be updated |
| `Albums`, `AlbumHandle` | the type accessor and the id-handle | the type has an endpoint |
| `AlbumArtistRelation`, `AlbumTracksRelation` | a relationship's reads and verbs | per relation with an endpoint |
| `AlbumTracksRelatedQuery` | how a to-many is filtered and sorted | its related read declares a filter or sort |
| `AlbumActions`, `AlbumCollectionActions` | custom actions | per action scope |

Each exists only where the document permits it: a type with no update has no `AlbumUpdate`, a
to-one relation has `set()` and no `add()`, and a capability the server does not offer is a method
that does not exist. The [client's documentation](https://haddowg.github.io/json-api-client/concepts/)
explains why.

## PHP 8.3 and 8.4

Accessors are always methods (`$album->title()`). For an 8.4 target each is also a get-only
property hook that delegates to its method (`$album->title`), so the two cannot drift. `--target`
picks one; the default is the running PHP. The runtime itself needs PHP 8.3.

## Guarantees

The generated code passes PHPStan at level 9 and PHP-CS-Fixer's PER-CS 2.0, and both are enforced
on the committed golden output. A string from the document reaches generated PHP only through an
escaping helper for its position, so a description or example can never become code.
