# Links a server builds are read through the runtime and are not typed

`self`, an action's link and the links of documents, collections and relationships are handed to
the runtime untyped, which reads the `href` of either a string or a link object, so the codegen
reads no links component for them. Links a resource type declares are the exception (ADR 0007).

## Considered options

A typed accessor per `asLink` action link has real value, since the link appears only when the
caller may run the action, so it doubles as a permission check. It was left out because the
generated action methods already reach the endpoint, the runtime's `_link()` already answers "is it
there", and the TypeScript codegen does not type links either. It stays open for both codegens
together.
