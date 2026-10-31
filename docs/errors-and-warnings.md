# Errors and warnings

A structure the codegen needs but cannot read refuses the document with a `SpecException` naming
the JSON pointer it was reading. Where a safe fallback keeps the generated API intact, the codegen
takes it and reports a warning through `Generator::warnings()` instead:

- an unusable `x-enum-varnames` entry gives way to the case name derived from the value;
- an unusable discriminator constant becomes `DISCRIMINATOR`;
- a paginator it cannot read generates the endpoint unpaginated (see [pagination](reading-the-document.md#pagination));
- an alias of a multi-method action that collides with another action's name leaves that method
  out;
- a JSON:API request body offered beside raw ones generates the document input alone.

An enum or error component whose name is not a usable PHP class name, and a type or member whose
PHP name is not usable, are refused.

## Where warnings appear

The CLI prints each warning to standard error as `Warning: …` and still writes the client. A
program driving `Generator` reads them from `Generator::warnings()` after `generate()`.

## Compatibility

Every document `haddowg/json-api` serves stamps `info.x-generator.contract`, an integer the server
raises whenever the structure it emits moves. This codegen reads a range of contracts:

- a document **older** than the range is refused, because it lacks structures the codegen relies on;
- a document **newer** than the range is generated with a warning that it may carry capabilities
  this version does not generate yet. Upgrade the codegen to pick them up.

A document without the stamp is read without the check. The codegen still targets documents
`haddowg/json-api` emits, with no generic mode for other producers: a structure it needs and cannot
find is refused with the JSON pointer it was reading.
