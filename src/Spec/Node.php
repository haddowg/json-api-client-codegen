<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Spec;

/**
 * One value inside the decoded document, carrying the JSON pointer it was reached by.
 *
 * Nothing in the codegen indexes the decoded array directly. Reading through a node means a
 * structure that is absent or of the wrong shape raises a {@see SpecException} naming its
 * pointer, at the point of the read, instead of surfacing as a TypeError later on.
 */
final class Node
{
    private function __construct(
        private readonly mixed $value,
        private readonly string $pointer,
    ) {}

    public static function root(mixed $value, string $pointer = ''): self
    {
        return new self($value, $pointer);
    }

    public function pointer(): string
    {
        return $this->pointer;
    }

    /**
     * The member at `$key`.
     *
     * @throws SpecException when this node is not an object, or declares no such member
     */
    public function get(string $key): self
    {
        return $this->find($key) ?? throw SpecException::missing($this->childPointer($key));
    }

    /** The member at `$key`, or null when it is absent. A member present as `null` or `false` is returned. */
    public function find(string $key): ?self
    {
        if (!\is_array($this->value) || !\array_key_exists($key, $this->value)) {
            return null;
        }

        return new self($this->value[$key], $this->childPointer($key));
    }

    /**
     * Walk a chain of members, stopping at the first absent one.
     *
     * `$schema->path('properties', 'type', 'const')` is the optional form of three chained
     * {@see get()} calls.
     */
    public function path(string ...$keys): ?self
    {
        $node = $this;
        foreach ($keys as $key) {
            $node = $node->find($key);
            if ($node === null) {
                return null;
            }
        }

        return $node;
    }

    /** @throws SpecException */
    public function string(): string
    {
        if (!\is_string($this->value)) {
            throw SpecException::wrongType($this->pointer, 'a string', $this->value);
        }

        return $this->value;
    }

    /** @throws SpecException */
    public function int(): int
    {
        if (!\is_int($this->value)) {
            throw SpecException::wrongType($this->pointer, 'an integer', $this->value);
        }

        return $this->value;
    }

    /** @throws SpecException */
    public function bool(): bool
    {
        if (!\is_bool($this->value)) {
            throw SpecException::wrongType($this->pointer, 'a boolean', $this->value);
        }

        return $this->value;
    }

    /**
     * This node's members, in document order.
     *
     * Keyed by `array-key` rather than `string` because PHP turns a numeric member name into an
     * int key — `responses` is the one that matters, and a caller reading `200` as a string is
     * the bug this signature prevents.
     *
     * @return array<array-key, self>
     *
     * @throws SpecException when this node is not an object
     */
    public function members(): array
    {
        if (!\is_array($this->value) || !$this->isObject()) {
            throw SpecException::wrongType($this->pointer, 'an object', $this->value);
        }

        $out = [];
        foreach ($this->value as $key => $member) {
            $out[$key] = new self($member, $this->childPointer((string) $key));
        }

        return $out;
    }

    /**
     * This node's array items, in order.
     *
     * @return list<self>
     *
     * @throws SpecException when this node is not an array
     */
    public function items(): array
    {
        if (!\is_array($this->value) || !\array_is_list($this->value)) {
            throw SpecException::wrongType($this->pointer, 'an array', $this->value);
        }

        $out = [];
        foreach ($this->value as $index => $item) {
            $out[] = new self($item, $this->childPointer((string) $index));
        }

        return $out;
    }

    /**
     * The string members of an array node.
     *
     * @return list<string>
     *
     * @throws SpecException when this node is not an array, or carries a non-string item
     */
    public function strings(): array
    {
        return \array_map(static fn(self $item): string => $item->string(), $this->items());
    }

    /** An empty JSON object and an empty array decode identically, so both answer true. */
    public function isObject(): bool
    {
        return \is_array($this->value) && ($this->value === [] || !\array_is_list($this->value));
    }

    public function isArray(): bool
    {
        return \is_array($this->value) && \array_is_list($this->value);
    }

    public function isString(): bool
    {
        return \is_string($this->value);
    }

    public function isFalse(): bool
    {
        return $this->value === false;
    }

    public function raw(): mixed
    {
        return $this->value;
    }

    private function childPointer(string $key): string
    {
        return $this->pointer === '' ? $key : $this->pointer . '.' . $key;
    }
}
