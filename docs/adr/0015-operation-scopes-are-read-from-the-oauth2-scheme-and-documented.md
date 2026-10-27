---
status: accepted
---

# Operation scopes are read from the oauth2 scheme and documented, not enforced

From contract 16 an operation that needs OAuth scopes says so in its `security`, under the scheme
the server configured for them, and that scheme lists the catalogue's scopes in its flows (core ADR
0182). The descriptor finds the scheme by its `type`, `oauth2`, in `components.securitySchemes`,
not by its name, since the name comes from the server's configuration. It reads the scopes listed
under that scheme in the operation's `security`, or the document's when the operation has none of
its own, in order and once each. Core fills the same scopes into every requirement naming the
scheme, so reading across requirements gives the one set it means. `scopes` is null when nothing
is listed, never an empty list, on each `OperationDescriptor`, `ActionDescriptor` and the
`AtomicDescriptor`. The serialised descriptor writes it only where present.

Each generated method states its scopes in one sentence between its prose and its tags, for
example "Needs an access token granting the `articles:read` and `people:read` scopes."

## Considered options

**Checking the granted scopes in the client** would need the token's scopes, which the client
never sees, and the server already refuses a short token with a `403 INSUFFICIENT_SCOPE` that
the runtime maps to `InsufficientScopeError`. A guard would only duplicate that answer.

## Consequences

The flows' scope descriptions are not read. Nothing generated uses them, and a client that wants a
token for a set of operations can collect their `scopes` from the descriptor.
