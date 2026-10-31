# Pagination comes from `x-paginator`, and a paginator the codegen cannot read warns

Every key a paginator reads, and a strategy's `kind`, can be renamed, so the strategy and wire keys
come from the page schema's `x-paginator` stamp, and names are consulted only when there is no stamp
(and then only the canonical ones). An unknown strategy, an unstamped non-canonical member set or an
unstamped menu generates the endpoint unpaginated with a warning, a deliberate exception to ADR
0001: an unknown strategy is a capability newer than the codegen rather than a malformed document,
and an unpaginated client is still correct. The TypeScript codegen follows the same rules.

## Considered options

Inferring any lone member as a fixed page read renamed selectors but misread every other single
custom parameter. Erroring blocked the whole client over one endpoint. Dropping a whole menu over
one unreadable arm would lose the readable ones, so only an unreadable default arm unpaginates the
collection.
