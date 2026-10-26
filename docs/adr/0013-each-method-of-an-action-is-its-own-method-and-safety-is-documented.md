---
status: accepted
---

# Each method of an action is its own method, and declared safety is documented

From contract 15 an action that answers to several methods has one operation id per method,
`action.<type>.<path>.<method>` (core ADR 0179). Before it every method shared one id, and the
descriptor generated only the preferred method, POST where advertised, under the action's name.
Now each method is its own `ActionDescriptor`, with `perMethod` true, keyed `<path>.<method>` in
its type's `actions`, and its own generated method named after that key: `export.get` and
`export.post` become `exportGet()` and `exportPost()`. A raw response class takes the same
name, so `ArticleExportGetCsvBody` can't meet the POST's. A single-method action is keyed and
named as it always was, and so is a multi-method action in a document from before contract 15,
whose shared id says it was projected as one operation. A client generated from either keeps
compiling.

Contract 15 also lets an action declare its safety as `x-safety`, an object holding whichever of
`readOnly`, `destructive` and `idempotent` the server declared. The descriptor reads them into an
`ActionSafety` on the action, each flag null when left out, and the action has no `ActionSafety`
when the operation declares none. The serialised descriptor writes only the declared flags. The
generated method's docblock says what was declared, for example "The server declares it
destructive." A flag that is not a boolean is an error by pointer (ADR 0001).

## Considered options

**Keeping the bare name for POST** and suffixing only the other methods would have left a client
generated from an older document compiling against a newer one. It also makes the POST look like
the action and the GET look like a variant of it, when the server treats them as peers, and it
breaks as soon as an action answers to two methods neither of which is POST. Mirroring the
operation id keeps the rule the same for every method.

**Typing safety into the signature**, for instance by refusing to generate a destructive action
without a confirmation argument, would turn a description into a guard the server never asked
for. The flags describe the action; they don't change how it is called.

## Consequences

A document whose multi-method action moves to contract 15 renames that action's generated
method, from `export()` to `exportPost()` beside a new `exportGet()`. That is the one rename, and
it happens only where the old client was silently missing a method. An action's own vendor
extensions, such as `x-workflows`, are not read, as no operation's are.
