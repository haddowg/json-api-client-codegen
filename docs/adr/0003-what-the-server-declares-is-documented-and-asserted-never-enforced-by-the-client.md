# What the server declares is documented and asserted, never enforced by the client

Validation constraints narrow the generated PHPDoc types where PHPStan can state them exactly (and
become docblock prose where it cannot), an operation's OAuth scopes and an action's `x-safety` flags
become a docblock sentence, and none of them is checked at runtime. The server enforces each of
them and is the only party that knows its current rules: a read asserts the narrowed type with an
inline `@var`, a write's narrowed type is a static contract only, and the server's `422` or `403`
stays the authority.

## Considered options

A runtime guard on a read would turn the server's legitimate data into a client failure the moment
its rules and the document drifted. Checking scopes would need the token's grants, which the
client never sees, and the runtime already maps `403 INSUFFICIENT_SCOPE` to its own error.
Requiring a confirmation argument on a destructive action would turn a description into a guard
the server never asked for.
