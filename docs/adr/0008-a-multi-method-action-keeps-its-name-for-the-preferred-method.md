# A multi-method action keeps its name for the preferred method

When an action answers to several methods with one operation id each, the preferred method (POST
where advertised, otherwise the first of GET, PUT, PATCH and DELETE) keeps the action's name and
every other method is keyed by an alias of the name and the method (`export` and `exportGet`).
This matches the TypeScript codegen key for key and never renames a method a client already calls.

## Considered options

Suffixing every method would treat the methods as peers, but would rename `export()` to
`exportPost()` under every existing caller.

## Consequences

The naming is asymmetric: `export()` sends POST and nothing in its name says so, only its
docblock. An alias that collides with another action's name is left out with a warning.
