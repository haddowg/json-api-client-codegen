---
status: accepted
---

# Links are read through the runtime and are not typed

Generator contract 7 renders the links a server builds as link objects, `{href, describedby}`,
and describes them per context: `<Type>ResourceLinks`, `<Type>DocumentLinks`,
`<Type>CollectionLinks` and `<Type><Rel>RelationshipLinks`, with a resource's `asLink` actions
named alongside `self`. A server can still opt back out to plain strings. Generated code never
reads a link value itself. It hands a document's `links` to the runtime, whose pagination reader,
`Document::link()`, `Relationship::link()` and `ResourceObject::_link()` all take the `href` of
either form through `Wire::href()`. So the codegen reads no links component, and the generated
client works against a contract 7 server and a server that renders strings alike.

## Considered options

A typed accessor per named link, such as `$album->_actionLink('reissue')` returning the `reissue`
URL when the server rendered it, was the one candidate with real value: an `asLink` action
appears only when the caller may run it, so the link doubles as a permission check. It was left
out because the generated `actions()` methods already reach the same endpoint by path, the
runtime's `_link('reissue')` already answers "is it there", and the TypeScript codegen doesn't
type `links` either. Adding it here alone would be a divergence to record and maintain for a
convenience. It stays open, for both codegens together.

## Consequences

Contract 6 needs no reader change either. Its nested read schemas list every member the server
always renders in `required`, and the nested shapes were already built from `required`, so the
read accessors, the array-shape aliases and the tagged-union branches drop the `?` on those
members while the write shapes keep the request body's own rules. A member a hook renders is
listed nowhere and stays optional. Documents before contract 6 list no nested read members, so
their output is unchanged.

Contract 7 also inlines an action or `202` body whose type the server doesn't register, where it
used to emit a `$ref` to a component that didn't exist. The reader still can't build a signature
from an open schema, so an action whose body or response names an undescribed type is an error
naming that schema's pointer (ADR 0001). The codegen reads no `202` body of a create or update,
so an inline one there changes nothing.
