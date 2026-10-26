---
status: accepted
---

# A multi-method action keeps its name for the preferred method

From contract 15 an action that answers to several methods has one operation id per method,
`action.<type>.<path>.<method>` (core ADR 0179). Before it every method shared one id, and the
descriptor read only the preferred method, POST where advertised and otherwise the first of GET,
PUT, PATCH and DELETE, under the action's name. That method still keeps the name, so no client
calling it breaks. Every other method whose operation id differs from the preferred one's is its
own `ActionDescriptor`, keyed in its type's `actions` by an `alias`: the action's name and the
method, joined the way the name joins its words. `export` over GET and POST gives `export` for the
POST and `exportGet` for the GET, and `bulk-import` over POST and PUT gives `bulk-import` and
`bulk-import-put`. The generated method and any raw response class are named from that key, so
`bulkImportPut()` and `ArticleExportGetCsvBody`. This matches the TypeScript codegen's ADR 0013
key for key. Core refuses two action ids that differ only in case or punctuation, so an alias
can't be another action's name. Should one be anyway, that method is left out with a warning.
Methods that share one id, as in an older document, still give only the preferred one.

Contract 15 also lets an action declare its safety as `x-safety`, an object holding whichever of
`readOnly`, `destructive` and `idempotent` the server declared. The descriptor reads them into an
`ActionSafety` on the action, each flag null when left out, and the action has no `ActionSafety`
when the operation declares none. The serialised descriptor writes only the declared flags, under
the `x-safety` names. The generated method's docblock says what was declared, for example "The
server declares it destructive." A flag that is not a boolean is an error by pointer (ADR 0001).

## Considered options

**Suffixing every method** would have matched the operation ids and treated the methods as peers.
It would also rename the one method a client generated before contract 15 already calls, from
`export()` to `exportPost()`, so it was rejected.

**Typing safety into the signature**, for instance by refusing to generate a destructive action
without a confirmation argument, would turn a description into a guard the server never asked
for. The flags describe the action and don't change how it is called.

## Consequences

The naming is asymmetric. `export()` sends POST while `exportGet()` sends GET, and nothing in the
first name says it is a POST. Its docblock does. An action's own vendor extensions, such as
`x-workflows`, are not read, as no operation's are.
