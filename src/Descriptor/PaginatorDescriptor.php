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
 *
 * `$bounds` carries each role's own schema keywords: from contract 5 a page member states its
 * `default` and, for a size, the cap the server serves. They narrow the generated parameters and
 * are not part of {@see matches()}, which decides whether two endpoints page the same way on the
 * wire.
 */
final class PaginatorDescriptor
{
    /** @var list<string> the wire keys, sorted; empty for a menu */
    public readonly array $parameters;

    /**
     * @param array<string, string>              $keys   role => wire key
     * @param array<string, PaginatorDescriptor> $arms   kind => strategy, in declaration order
     * @param array<string, Constraints>         $bounds role => the keywords its schema declares
     */
    public function __construct(
        public readonly PaginatorKind $kind,
        public readonly array $keys = [],
        public readonly array $arms = [],
        public readonly ?string $default = null,
        public readonly string $discriminator = 'kind',
        public readonly array $bounds = [],
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

    /**
     * The same strategy with each role's keywords, read from the schema of its wire key.
     *
     * @param array<string, Constraints> $byKey wire key => the keywords its schema declares
     */
    public function bounded(array $byKey): self
    {
        $bounds = [];
        foreach ($this->keys as $role => $key) {
            $constraints = $byKey[$key] ?? null;
            if ($constraints !== null && !$constraints->isEmpty()) {
                $bounds[$role] = $constraints;
            }
        }
        \ksort($bounds, \SORT_STRING);

        return new self($this->kind, $this->keys, $this->arms, $this->default, $this->discriminator, $bounds);
    }

    /** The keywords a role's schema declares, empty when it declares none. */
    public function bounds(string $role): Constraints
    {
        return $this->bounds[$role] ?? Constraints::none();
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
