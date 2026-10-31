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
