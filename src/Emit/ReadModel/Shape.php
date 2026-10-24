<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

use haddowg\JsonApiCodegen\Descriptor\Constraints;
use haddowg\JsonApiCodegen\Descriptor\ValueSchema;
use haddowg\JsonApiCodegen\Emit\Narrowing;

/**
 * A value schema reduced to the shape generated code reads it as.
 *
 * Composition follows each keyword's own semantics: a `oneOf` whose object branches each name
 * their `discriminator` value with a `const` is a tagged union; an `anyOf` is one merged object
 * with every branch member optional, because the branches are not exclusive; an `allOf` is
 * flattened and keeps its branches' required members; and a `{"type": "null"}` branch makes the
 * value nullable rather than becoming a branch of its own. Anything else with no declared type
 * is `mixed`, which is honest: the branches are the whole description.
 *
 * A scalar or a list keeps its schema's constraints, and its wire type is narrowed by them (see
 * {@see Narrowing}). The native type never is.
 */
final class Shape
{
    /**
     * @param array<string, array{shape: Shape, required: bool}> $members  an object's members, in document order
     * @param list<array{value: string, shape: Shape}>           $branches a tagged union's object branches
     */
    private function __construct(
        public readonly ShapeKind $kind,
        public readonly bool $nullable,
        public readonly ?string $scalar = null,
        public readonly array $members = [],
        public readonly ?self $items = null,
        public readonly ?string $discriminator = null,
        public readonly array $branches = [],
        public readonly Constraints $constraints = new Constraints(),
    ) {}

    public static function of(ValueSchema $schema): self
    {
        $nullable = \in_array('null', $schema->types, true);
        $own = self::declared($schema);

        $composed = null;
        if ($schema->oneOf !== []) {
            [$branches, $null] = self::withoutNull($schema->oneOf);
            $nullable = $nullable || $null;
            $composed = self::tagged($schema->discriminator, $branches)
                ?? (\count($branches) === 1 ? self::of($branches[0]) : self::unknown());
        } elseif ($schema->anyOf !== []) {
            [$branches, $null] = self::withoutNull($schema->anyOf);
            $nullable = $nullable || $null;
            $composed = \count($branches) === 1
                ? self::of($branches[0])
                : self::merge(\array_map(self::of(...), $branches), true);
        }

        if ($schema->allOf !== []) {
            $flat = self::merge(\array_map(self::of(...), $schema->allOf), false);
            $composed = $composed === null ? $flat : self::merge([$composed, $flat], false);
        }

        $shape = match (true) {
            $composed === null => $own ?? self::unknown(),
            $own === null => $composed,
            $own->kind === ShapeKind::Object && $composed->kind === ShapeKind::Object => self::merge([$own, $composed], false),
            default => $composed,
        };

        if ($composed !== null && \in_array($shape->kind, [ShapeKind::Scalar, ShapeKind::List], true)) {
            $shape = $shape->constrained($schema->constraints->orElse($shape->constraints));
        }

        return $shape->nullable($nullable || $shape->nullable);
    }

    /** The same shape, with a tagged union read as the merged object a nested value is given. */
    public function merged(): self
    {
        if ($this->kind !== ShapeKind::Tagged) {
            return $this;
        }

        return self::merge(\array_map(static fn(array $branch): Shape => $branch['shape'], $this->branches), true)->nullable($this->nullable);
    }

    public function nullable(bool $nullable): self
    {
        return new self($this->kind, $nullable, $this->scalar, $this->members, $this->items, $this->discriminator, $this->branches, $this->constraints);
    }

    public function constrained(Constraints $constraints): self
    {
        return new self($this->kind, $this->nullable, $this->scalar, $this->members, $this->items, $this->discriminator, $this->branches, $constraints);
    }

    /** Whether the constraints narrow the wire type past what the JSON type alone says. */
    public function narrowed(): bool
    {
        return $this->wireType() !== $this->constrained(Constraints::none())->wireType()
            || ($this->kind === ShapeKind::List && ($this->items ?? self::unknown())->merged()->narrowed());
    }

    /**
     * The PHPDoc type of the value on the wire: what a read returns for anything but a tagged
     * union, and what a write accepts for every shape.
     */
    public function wireType(): string
    {
        $type = match ($this->kind) {
            ShapeKind::Mixed => 'mixed',
            ShapeKind::Scalar => \in_array($this->scalar, ['integer', 'string'], true)
                ? Narrowing::scalar((string) $this->scalar, $this->constraints)
                : self::scalarType((string) $this->scalar),
            ShapeKind::List => Narrowing::list(($this->items ?? self::unknown())->merged()->wireType(), $this->constraints),
            ShapeKind::Object => $this->members === [] ? 'array<string, mixed>' : self::objectType($this->members, []),
            ShapeKind::Tagged => \implode('|', \array_map(
                fn(array $branch): string => self::objectType($branch['shape']->members, [(string) $this->discriminator => '\'' . \addcslashes($branch['value'], "'\\") . '\'']),
                $this->branches,
            )),
        };

        return $this->nullable && $this->kind !== ShapeKind::Mixed ? $type . '|null' : $type;
    }

    /** Whether the wire type names an array shape, which is what earns a `@phpstan-type` alias. */
    public function hasShape(): bool
    {
        return match ($this->kind) {
            ShapeKind::Object => $this->members !== [],
            ShapeKind::Tagged => true,
            ShapeKind::List => ($this->items ?? self::unknown())->merged()->hasShape(),
            default => false,
        };
    }

    public static function scalarType(string $json): string
    {
        return match ($json) {
            'integer' => 'int',
            'number' => 'float',
            'boolean' => 'bool',
            default => 'string',
        };
    }

    /** A member name as an array-shape key, quoted when it is not a bare identifier. */
    public static function key(string $name): string
    {
        return \preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) === 1 ? $name : '\'' . \addcslashes($name, "'\\") . '\'';
    }

    /** A value the document declares nothing about. */
    public static function unknown(): self
    {
        return new self(ShapeKind::Mixed, false);
    }

    /**
     * @param array<string, array{shape: Shape, required: bool}> $members
     * @param array<string, string>                              $literals members pinned to a literal type
     */
    private static function objectType(array $members, array $literals): string
    {
        $parts = [];
        foreach ($members as $name => $member) {
            if (isset($literals[$name])) {
                $parts[] = self::key($name) . ': ' . $literals[$name];
                unset($literals[$name]);
                continue;
            }
            $parts[] = self::key($name) . ($member['required'] ? '' : '?') . ': ' . $member['shape']->merged()->wireType();
        }
        foreach ($literals as $name => $literal) {
            $parts[] = self::key($name) . ': ' . $literal;
        }

        return 'array{' . \implode(', ', $parts) . '}';
    }

    /** The shape a schema's own `type`, `properties` and `items` declare, ignoring composition. */
    private static function declared(ValueSchema $schema): ?self
    {
        $type = null;
        foreach ($schema->types as $candidate) {
            if ($candidate !== 'null') {
                $type = $candidate;
                break;
            }
        }

        return match ($type) {
            null => null,
            'object' => new self(ShapeKind::Object, false, members: self::members($schema)),
            'array' => new self(ShapeKind::List, false, items: $schema->items === null ? self::unknown() : self::of($schema->items), constraints: $schema->constraints),
            default => new self(ShapeKind::Scalar, false, scalar: $type, constraints: $schema->constraints),
        };
    }

    /** @return array<string, array{shape: Shape, required: bool}> */
    private static function members(ValueSchema $schema): array
    {
        $out = [];
        foreach ($schema->properties as $name => $property) {
            $out[$name] = ['shape' => self::of($property), 'required' => \in_array($name, $schema->required, true)];
        }

        return $out;
    }

    /**
     * @param list<ValueSchema> $branches
     *
     * @return array{list<ValueSchema>, bool} the branches that are not `{"type": "null"}`, and whether one was
     */
    private static function withoutNull(array $branches): array
    {
        $out = [];
        $null = false;
        foreach ($branches as $branch) {
            if ($branch->types === ['null'] && !$branch->composed() && $branch->properties === []) {
                $null = true;
                continue;
            }
            $out[] = $branch;
        }

        return [$out, $null];
    }

    /**
     * A tagged union, when every branch is an object naming its discriminator value with a
     * `const`. Null when the union is not discriminated that way.
     *
     * @param list<ValueSchema> $branches
     */
    private static function tagged(?string $discriminator, array $branches): ?self
    {
        if ($discriminator === null || $branches === []) {
            return null;
        }

        $out = [];
        foreach ($branches as $branch) {
            $value = ($branch->properties[$discriminator] ?? null)?->const;
            $shape = self::of($branch);
            if ($value === null || $shape->kind !== ShapeKind::Object) {
                return null;
            }
            $out[] = ['value' => $value, 'shape' => $shape];
        }

        return new self(ShapeKind::Tagged, false, discriminator: $discriminator, branches: $out);
    }

    /**
     * One object from several. Members keep the order they are first seen in; a member two
     * branches declare differently becomes `mixed`, since neither declaration alone is true.
     *
     * @param list<Shape> $shapes
     */
    private static function merge(array $shapes, bool $optional): self
    {
        $members = [];
        $nullable = false;
        foreach ($shapes as $shape) {
            $shape = $shape->merged();
            if ($shape->kind !== ShapeKind::Object) {
                return self::unknown();
            }
            $nullable = $nullable || $shape->nullable;
            foreach ($shape->members as $name => $member) {
                $required = $member['required'] && !$optional;
                if (!isset($members[$name])) {
                    $members[$name] = ['shape' => $member['shape'], 'required' => $required];
                    continue;
                }
                $known = $members[$name]['shape'];
                $members[$name] = [
                    'shape' => $known->wireType() === $member['shape']->wireType() ? $known : self::unknown(),
                    'required' => $members[$name]['required'] || $required,
                ];
            }
        }

        return new self(ShapeKind::Object, $nullable, members: $members);
    }
}
