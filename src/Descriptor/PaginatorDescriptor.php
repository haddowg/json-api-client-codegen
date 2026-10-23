<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The pagination one endpoint advertises: the strategy, and the wire key that fills each of its
 * roles.
 *
 * Every key is server-configurable (`withPageKey()`, `withSizeKey()`, and so on), so a generated
 * method names its parameters by role and puts the configured key on the wire. A client that
 * hardcoded `page[number]` would break against a server that renamed it.
 *
 * A {@see PaginatorKind::Multi} menu carries no keys of its own. It carries its arms, keyed by
 * the `kind` that selects each one, the discriminator member that carries that kind, and the
 * arm the server uses when a request names none.
 */
final class PaginatorDescriptor
{
    /** @var list<string> the wire keys, sorted; empty for a menu */
    public readonly array $parameters;

    /**
     * @param array<string, string>              $keys role => wire key
     * @param array<string, PaginatorDescriptor> $arms kind => strategy, in declaration order
     */
    public function __construct(
        public readonly PaginatorKind $kind,
        public readonly array $keys = [],
        public readonly array $arms = [],
        public readonly ?string $default = null,
        public readonly string $discriminator = 'kind',
    ) {
        $parameters = \array_values($keys);
        \sort($parameters, \SORT_STRING);
        $this->parameters = $parameters;
    }

    public static function none(): self
    {
        return new self(PaginatorKind::None);
    }

    /**
     * @param array<string, PaginatorDescriptor> $arms
     */
    public static function menu(array $arms, string $default, string $discriminator): self
    {
        return new self(PaginatorKind::Multi, [], $arms, $default, $discriminator);
    }

    public function paginated(): bool
    {
        return $this->kind !== PaginatorKind::None;
    }

    /** The wire key a role is configured under, or null when the paginator has no such role. */
    public function key(string $role): ?string
    {
        return $this->keys[$role] ?? null;
    }

    /**
     * The strategy a request that chooses nothing gets: a menu's default arm, otherwise this.
     */
    public function unchosen(): self
    {
        return $this->kind === PaginatorKind::Multi && $this->default !== null ? $this->arms[$this->default] ?? $this : $this;
    }

    public function matches(self $other): bool
    {
        if ($this->kind !== $other->kind || $this->keys != $other->keys || $this->default !== $other->default
            || $this->discriminator !== $other->discriminator || \array_keys($this->arms) !== \array_keys($other->arms)) {
            return false;
        }

        foreach ($this->arms as $kind => $arm) {
            if (!$arm->matches($other->arms[$kind])) {
                return false;
            }
        }

        return true;
    }
}
