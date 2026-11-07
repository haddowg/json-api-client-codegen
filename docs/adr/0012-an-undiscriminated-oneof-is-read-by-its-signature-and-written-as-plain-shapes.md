# An undiscriminated oneOf is read by its signature and written as plain shapes

Core projects a `OneOf` with no discriminator as a `oneOf` of closed object branches, each requiring a member no other branch declares and naming its variant in its `title`. The codegen reads it as one class per branch, picked by that signature member, so a read is narrowed the same way a discriminated union's is. Its write input stays a plain union of array shapes, because sealing them with `...<string, never>` made things worse: PHPStan 2.0 and 2.1 ignore the seal, and from 2.3 a union of sealed shapes collapses into one all-optional shape that loses the required-member check and breaks `array_key_exists` narrowing.

## Consequences

A write mixing two branches, or carrying an undeclared key, passes PHPStan's default analysis and is rejected by the server; PHPStan's bleeding-edge mode catches both with the plain shapes. A `oneOf` whose branches have no signature, or are not objects, still reads as `mixed`.
