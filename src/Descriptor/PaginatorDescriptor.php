<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The pagination one endpoint advertises: the strategy, and the `page[…]` members it accepts.
 *
 * The members are carried because their names are server-configurable — every paginator takes a
 * key override (`withPageKey()`, `withSizeKey()`, and so on). The kind says which pagination
 * surface to generate; the members say what to actually put on the wire, and a generated client
 * that hardcoded `page[number]` would break against a server that renamed it.
 *
 * They are the page member keys (`number`, `size`), not the full `page[number]` spelling, so
 * they read the same whether the document declares the members as one `page` object parameter or
 * as flattened `page[…]` parameters.
 */
final class PaginatorDescriptor
{
    /** @param list<string> $parameters page member keys, sorted */
    public function __construct(
        public readonly PaginatorKind $kind,
        public readonly array $parameters,
    ) {}

    public static function none(): self
    {
        return new self(PaginatorKind::None, []);
    }

    public function paginated(): bool
    {
        return $this->kind !== PaginatorKind::None;
    }

    public function accepts(string $member): bool
    {
        return \in_array($member, $this->parameters, true);
    }

    public function matches(self $other): bool
    {
        return $this->kind === $other->kind && $this->parameters === $other->parameters;
    }
}
