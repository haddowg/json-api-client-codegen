---
status: accepted
---

# Pagination comes from `x-paginator`, and a paginator the codegen cannot read warns

Every key a paginator reads can be renamed, and so can a strategy's `kind`, so a strategy guessed
from member names was wrong for any server that used either option. Generator contract 3 stamps
each page schema with `x-paginator`, naming the strategy and the key that fills each role, and a
`MultiPaginator` menu with the kind it defaults to. When the stamp is there, it decides the
strategy and the wire keys, and names are never consulted. Without it, the codegen infers from the
canonical names only: `number` and `size`, `offset` and `limit`, a cursor bound with an optional
`size`, and a lone `number` for a fixed page. Anything else, a strategy the codegen does not know,
or a menu with no stamp is a build-time warning, and that endpoint generates unpaginated. The
TypeScript codegen follows the same rules.

This is a deliberate exception to ADR 0001. That ADR errors on an unreadable structure because a
dropped capability would surface weeks later as an undefined method. Pagination differs in two
ways. An unknown strategy is a capability newer than the codegen rather than a malformed document,
which is the case the contract range already treats as a warning. And the unpaginated fallback is
still a correct client: it reads the first page the server returns and offers no paging method
that would send parameters the server does not read. A malformed stamp, such as a `strategy` that
is not a string, is still an error.

## Considered options

**Inferring from any lone member as a fixed page**, as the codegen did before, read a renamed page
selector correctly and also read any other single custom parameter as one. The stamp now covers the
renamed case, so the guess is no longer worth its false positives.

**Erroring on an unknown member set**, as before, blocked the whole client over one endpoint, and
diverged from the TypeScript codegen for no benefit the warning does not give.

**Dropping a whole menu when one arm cannot be read** would lose the arms that can. Only the
default arm is load-bearing, because an unpicked read is typed as its page, so an unreadable
default makes the collection unpaginated and any other unreadable arm is just left unpickable.

## Consequences

A contract-2 server whose paginator renames a key now generates that endpoint unpaginated with a
warning, where a renamed fixed page used to be read correctly. Upgrading the server to contract 3
restores it.

The descriptor carries warnings of its own, and `Generator::warnings()` reports them ahead of the
emitters'.
