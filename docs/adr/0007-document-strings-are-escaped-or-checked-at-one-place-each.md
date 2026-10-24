---
status: accepted
---

# Document strings are escaped or checked at one place each

A string read from the document reaches generated PHP in one of three positions, and each has
one helper. A string literal goes through `Php::string()`. Anything inside a docblock goes through
`Php::docblock()`, `Php::comment()` or the assembler's tag insertion, which break up `*/` and turn
line breaks into spaces; a literal type or array-shape key inside one goes through
`Php::docString()` or `Php::docKey()`, which double-quote and escape a value PHPStan must still
read back exactly. A name becomes an identifier only after `Identifier` accepts it for its
position: a class can't be called `List`, an enum case can't be called `class`, and a property
can't be `$this`.

Where a safe fallback keeps the generated API intact, the codegen takes it and warns: an unusable
`x-enum-varnames` entry gives way to the case name derived from the value, and an unusable
discriminator constant becomes `DISCRIMINATOR`. Where it doesn't, the document is refused with a
`SpecException` naming the component or type: an enum or error component whose name isn't a class
name, and a type or member whose PHP name isn't usable.

## Considered options

Escaping at each call site was how the codegen started, and it missed literal types written into
`@phpstan-type` tags, which went through the string-literal helper rather than a docblock one.

## Consequences

Every type, member and component name the projector emits already passes, so the music-catalog
output is unchanged.
