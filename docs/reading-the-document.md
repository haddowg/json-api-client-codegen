# Reading the document

How each part of a `haddowg/json-api` OpenAPI document becomes generated code. The surface itself,
and the runtime under it, are described in the
[client's documentation](https://haddowg.github.io/json-api-client/).

## Pagination

The page schema's `x-paginator` stamp decides the strategy and the wire key for each role. Without
a stamp, only the canonical member names are recognised: `number` and `size`, `offset` and `limit`,
a cursor bound with an optional `size`, and a lone `number` for a fixed page. An unknown strategy,
any other unstamped member set, or an unstamped menu generates that endpoint unpaginated with a
warning. In a menu, an unreadable default arm unpaginates the collection; any other unreadable arm
is just not offered. A malformed stamp, such as a `strategy` that is not a string, is an error.

## The error catalogue

Error codes are read off the error documents. `ErrorDocument`, each `ErrorDocument<NNN>` and each
narrowed `ErrorDocument<NNN>With<Suffix>` offer shapes as `errors.items.anyOf` references after the
generic `Error`; a shape narrows `Error` in an `allOf`, pinning `status`, requiring at most one
`source` member (`pointer`, `parameter` or `header`) and optionally declaring a closed `meta`.
Each `const` branch of its `code` is a code, carrying its title and description. A code's example
`detail` is the `detail` of the error in its `components.examples` entry. A code the narrowed
documents offer and its status's shared document does not is scoped, and the operations whose
responses point at those documents are recorded on it.

A shape that does not narrow `code` to `const` branches, as a document cataloguing one component
per code does, is refused with the pointer it was reading.

## Sort families and structured lists

A `sort` parameter's fixed keys come from its `enum`, or from the `enum` branch of an `anyOf` when
it also accepts families of keys by prefix. Each family in `x-sort-families` becomes a
`sortBy<Prefix>(string $suffix, bool $descending = false)` that checks the key against the family's
pattern, and `sortRaw()` accepts a key of any family.

A list attribute whose `items` is an object reads as a list of array shapes, each element narrowed
member by member, and one whose `items` is a `oneOf` as a list of the union's branch classes (see
below). An element of the wrong shape throws `MalformedDocument`.

## Deprecation

Anything the document marks `deprecated` gets `@deprecated` on every generated member that reads,
writes or calls it: an attribute's accessor and setters, a relation's accessors and setters, an
operation's or action's method, a filter's `where…()` and a sort family's `sortBy…()`. Sort keys
in `x-deprecated-sorts` are named in a sentence on `sort()`, since a literal token carries no tag.
A type whose every endpoint is deprecated gets the tag on its client property.

## Attribute unions

A `oneOf` attribute whose branches are objects reads as one generated class per branch, and its
accessor returns their union, so a caller narrows it with `instanceof` or `match`. The branch a
value belongs to is found one of two ways:

- **By a discriminator.** A `oneOf` with a `discriminator` picks the branch whose `const` the
  discriminator member holds, and each branch class carries that value as a constant.
- **By a signature.** A `oneOf` without one picks the branch whose signature the value carries:
  a member that branch requires and no other branch declares. Each branch class is named by the
  branch's `title`. A value carrying no branch's signature, or two, is a `MalformedDocument`.

A `oneOf` whose branches can't be told apart either way, because a branch is not an object or has
no signature, reads as `mixed`. On writes each union is a plain union of its branches' array
shapes, such as `array{radius: int}|array{width: int, height: int}`. The shapes are not sealed,
because a union of sealed shapes collapses into one all-optional shape in PHPStan, so a value
mixing two branches passes static analysis and is rejected by the server.

## Constraints, scopes and safety

Constraints narrow PHPDoc types where PHPStan can state them exactly, and the native type never
changes:

- integer bounds become `int<min, max>`, or `positive-int` for a floor of 1;
- a `minLength` of at least 1 becomes `non-empty-string`;
- a `minItems` of at least 1 becomes `non-empty-list`.

Any other constraint (`pattern`, `format`, `maxLength`, `multipleOf`, `uniqueItems`, a `number`'s
bounds), and a page member's `default`, becomes a sentence in the accessor's or setter's docblock.
Members of nested objects and tagged-union branches are narrowed but get no sentence.

An operation that needs OAuth scopes states them in one docblock sentence, for example "Needs an
access token granting the `articles:read` and `people:read` scopes." The scopes come from the
operation's `security` (or the document's) under the `oauth2` scheme. An action's declared
`x-safety` flags (`readOnly`, `destructive`, `idempotent`) are stated the same way, for example
"The server declares it destructive." None of these is checked at runtime.

## Operation examples

Each generated operation method closes its docblock with an `@example` quoting the operation's
request body example when it has one, else its first success response's, as pretty-printed JSON.
An example longer than 60 lines is left out rather than truncated. Error responses are not quoted,
since their examples are shared across operations. A `*/` inside a string value is written as
`*\/`, which JSON reads as the same slash, so the quoted example still parses.

## Custom actions

A raw (non-JSON:API) media type is read as one of these kinds, by its schema first and its media
type second:

| Kind | Decided by | Response type | Request type |
|---|---|---|---|
| binary | `format: binary`, or nothing else matches | `StreamInterface` | `StreamInterface\|string` |
| JSON | `application/json` or any `+json` type | the schema's shape | the schema's array shape |
| text | a string schema, or `text/*` with no schema | `string` | `string` |
| multipart | `multipart/form-data` request body | | field array with `FilePart` files |
| urlencoded | `application/x-www-form-urlencoded` request body | | field array |

A JSON response whose schema is an object with members becomes a value class named after its
schema component, falling back to `<Type><Action><Subtype>Body` on a collision. A form's schema
`properties` narrow its field array to an array shape, and `additionalProperties` types any other
field. A form that is an action's only request media type drops the `$contentType` parameter.

An action answering to several methods keeps its name for the preferred one (POST, else the first
of GET, PUT, PATCH and DELETE); each other method is named from the action and the method,
`export` and `exportGet`, `bulk-import` and `bulk-import-put`.

An action that answers with a URL returns the runtime's `ActionLocation`, or a union with its
`200` result when it declares one too. The URL is never fetched.

An action's result is its first 2xx JSON:API document, so one answering `201 Created` with the
resource it created reads that resource as one answering `200` does. An action declaring both a
`200` and a `201` document that name different types is typed by the `200`, with a warning.

A collection action's `filter[…]` parameters become a trailing `array $filter = []`, typed by an
array shape of exactly those filters, each value typed as the collection read types it. The shape
is spelled out on the action rather than borrowing the read's alias, so it stands even where the
read is not exposed. The names, and a structured filter's members, are checked at run time through
the runtime's `FilterGuard`, and the filters are sent as a read sends them.

## Relations, pivots and links

A relation whose linkage is a union is polymorphic even with one member type, and rejects a related
resource of an undeclared type. The union is a `oneOf` discriminated by `type`, or in an older
document an `anyOf`; both read the same. Included resources are read through each relation's
accessor, so a document's typed `included` adds nothing to the generated client. A pivot relation's response fields are read through
`<rel>Pivots()` (or `<rel>Pivot()`) on the parent; a field the response schema does not require
reads as nullable.

The links a type declares are typed through `_declaredLinks()`; every other link is read through
the runtime's untyped `_links()` and `_link()`.

## Atomic operations

The atomic endpoint is the one whose request body declares the `ext="https://jsonapi.org/ext/atomic"`
media type. When the document has one, the root client gets `atomic()`, which runs a batch against
that endpoint's path, and every relation setter on a write input also accepts a `Created` handle of
the related type. Without one there is no `atomic()`, and setters take only resources and
identifiers. Every create and update input implements the runtime's `CreateInput` or `UpdateInput`
whether or not the server has the extension.

## Relationship Queries

A to-many's filters and sort tokens are read from its related read (`GET /albums/{id}/tracks`),
and a read operation that declares the `relatedQuery` parameter supplies the profile URI from its
`x-profile`. A relation that has something to filter or sort by and is a depth-1 include of such a
read gets a `<Type><Relation>RelatedQuery`, holding that vocabulary as its own constants and
aliases, and the read's `withX()` takes a closure over it. The collection query carries those
closures directly; a single read that declares `relatedQuery` gets a `<Type>ReadProjection`,
because the write projection is shared with writes, which do not.

The same class is generated for every to-many whose related read declares a filter or sort, and
that relation's `related()` takes the same closure, sent as the endpoint's own `filter[…]` and
`sort` without the profile.

## Handles on fetched resources

Every resource base whose type has an id-handle gets `_handle()`, which returns that handle over
the fetcher that read the resource. A type has an id-handle when it has a single-resource read,
update or delete, a relation with a relationship handle, or a resource-scoped action.
