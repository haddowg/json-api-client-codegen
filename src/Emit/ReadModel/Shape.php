<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

use haddowg\JsonApiCodegen\Descriptor\Constraints;
use haddowg\JsonApiCodegen\Descriptor\ValueSchema;
use haddowg\JsonApiCodegen\Emit\Narrowing;
use haddowg\JsonApiCodegen\Emit\Php;

/**
 * A value schema reduced to the shape generated code reads it as.
 *
 * Composition follows each keyword's own semantics: a `oneOf` whose object branches each name
 * their `discriminator` value with a `const` is a tagged union, and so is a `oneOf` with no
 * discriminator whose object branches each require a member no other branch declares (its
 * signature, which selects it); an `anyOf` is one merged object
 * with every branch member optional, because the branches are not exclusive; an `allOf` is
 * flattened and keeps its branches' required members; and a `{"type": "null"}` branch makes the
 * value nullable rather than becoming a branch of its own. Anything else with no declared type
 * is `mixed`, which is honest: the branches are the whole description.
 *
 * A `type` array keeps every type it lists: its scalars are one scalar shape of several types, and
 * scalars mixed with an array or an object are a choice between those shapes, picked by the
 * value's JSON type. A `oneOf` or `anyOf` whose branches each take JSON types no other branch
 * takes is the same choice. Each keyword holds only for values of its own type, as JSON Schema
 * defines it, so `minLength` narrows the string and leaves the number alone.
 *
 * A scalar or a list keeps its schema's constraints, and its wire type is narrowed by them (see
 * {@see Narrowing}). The native type never is.
 */
final class Shape
{
    private const array SCALARS = ['string', 'integer', 'number', 'boolean'];

    /**
     * @param list<string>                                       $scalars  a scalar's JSON types, in document order
     * @param array<string, array{shape: Shape, required: bool}> $members  an object's members, in document order
     * @param list<array{value: string, shape: Shape, signature: list<string>}> $branches a tagged union's
     *                                                                                   object branches; the
     *                                                                                   signature selects one
     *                                                                                   when there is no
     *                                                                                   discriminator
     * @param list<Shape>                                        $alternatives a choice's shapes, none nullable,
     *                                                                         each taking JSON types no other takes
     */
    private function __construct(
        public readonly ShapeKind $kind,
        public readonly bool $nullable,
        public readonly array $scalars = [],
        public readonly array $members = [],
        public readonly ?self $items = null,
        public readonly ?string $discriminator = null,
        public readonly array $branches = [],
        public readonly Constraints $constraints = new Constraints(),
        public readonly array $alternatives = [],
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
                ?? (\count($branches) === 1 ? self::of($branches[0]) : (self::choice(\array_map(self::of(...), $branches)) ?? self::unknown()));
        } elseif ($schema->anyOf !== []) {
            [$branches, $null] = self::withoutNull($schema->anyOf);
            $nullable = $nullable || $null;
            $shapes = \array_map(self::of(...), $branches);
            $composed = \count($branches) === 1
                ? $shapes[0]
                : (self::choice($shapes) ?? self::merge($shapes, true));
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
        } elseif ($composed !== null && $shape->kind === ShapeKind::Choice) {
            $shape = $shape->choosing(\array_map(
                static fn(Shape $alternative): Shape => $alternative->constrained($schema->constraints->orElse($alternative->constraints)),
                $shape->alternatives,
            ));
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
        return new self($this->kind, $nullable, $this->scalars, $this->members, $this->items, $this->discriminator, $this->branches, $this->constraints, $this->alternatives);
    }

    /** The same shape under `$constraints`; a choice puts them on every alternative. */
    public function constrained(Constraints $constraints): self
    {
        if ($this->kind === ShapeKind::Choice) {
            return $this->choosing(\array_map(static fn(Shape $alternative): Shape => $alternative->constrained($constraints), $this->alternatives));
        }

        return new self($this->kind, $this->nullable, $this->scalars, $this->members, $this->items, $this->discriminator, $this->branches, $constraints, $this->alternatives);
    }

    /** Whether the constraints narrow the wire type past what the JSON type alone says. */
    public function narrowed(): bool
    {
        if ($this->kind === ShapeKind::Choice) {
            return \array_filter($this->alternatives, static fn(Shape $alternative): bool => $alternative->narrowed()) !== [];
        }

        return $this->wireType() !== $this->constrained(Constraints::none())->wireType()
            || ($this->kind === ShapeKind::List && ($this->items ?? self::unknown())->merged()->narrowed());
    }

    /**
     * The JSON types a value of this shape can have, `null` aside, or null for `mixed` and a
     * tagged union, which take values no type list describes.
     *
     * @return list<string>|null
     */
    public function jsonTypes(): ?array
    {
        return match ($this->kind) {
            ShapeKind::Scalar => $this->scalars,
            ShapeKind::List => ['array'],
            ShapeKind::Object => ['object'],
            ShapeKind::Choice => \array_merge(...\array_map(static fn(Shape $alternative): array => $alternative->jsonTypes() ?? [], $this->alternatives)),
            default => null,
        };
    }

    /**
     * Whether a scalar's integer value is read as a float: a `number` is a float, unless the
     * scalar takes an `integer` too, which keeps it an int.
     */
    public function readsIntAsFloat(): bool
    {
        return \in_array('number', $this->scalars, true) && !\in_array('integer', $this->scalars, true);
    }

    /**
     * A scalar's PHPDoc type as it arrives, before {@see readsIntAsFloat()} turns an integer
     * into a float.
     */
    public function rawType(): string
    {
        return self::union(\array_map(
            fn(string $json): string => $json === 'number' && $this->readsIntAsFloat() ? 'int|float' : self::scalarDoc($json, $this->constraints),
            $this->scalars,
        ));
    }

    /**
     * The PHPDoc type of the value on the wire: what a read returns for anything but a tagged
     * union, and what a write accepts for every shape.
     */
    public function wireType(): string
    {
        $type = match ($this->kind) {
            ShapeKind::Mixed => 'mixed',
            ShapeKind::Scalar => self::union(\array_map(fn(string $json): string => self::scalarDoc($json, $this->constraints), $this->scalars)),
            ShapeKind::Choice => self::union(\array_map(static fn(Shape $alternative): string => $alternative->wireType(), $this->alternatives)),
            ShapeKind::List => Narrowing::list(($this->items ?? self::unknown())->wireType(), $this->constraints),
            ShapeKind::Object => $this->members === [] ? 'array<string, mixed>' : self::objectType($this->members, []),
            ShapeKind::Tagged => \implode('|', \array_map(
                fn(array $branch): string => self::objectType($branch['shape']->members, $this->discriminator === null ? [] : [$this->discriminator => Php::docString($branch['value'])]),
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
            ShapeKind::Choice => \array_filter($this->alternatives, static fn(Shape $alternative): bool => $alternative->hasShape()) !== [],
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

    /**
     * The native type of a scalar of each of `$scalars`, as one union.
     *
     * @param list<string> $scalars
     */
    public static function scalarNative(array $scalars): string
    {
        return self::union(\array_map(self::scalarType(...), $scalars));
    }

    /** A member name as an array-shape key, quoted when it is not a bare identifier. */
    public static function key(string $name): string
    {
        return Php::docKey($name);
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

    /**
     * The shape a schema's own `type`, `properties` and `items` declare, ignoring composition.
     * Every type the `type` array lists is kept: its scalars as one scalar shape, and scalars
     * beside an array or an object as a choice.
     */
    private static function declared(ValueSchema $schema): ?self
    {
        $types = \array_values(\array_unique($schema->types));
        $alternatives = [];

        $scalars = \array_values(\array_intersect($types, self::SCALARS));
        if ($scalars !== []) {
            $alternatives[] = new self(ShapeKind::Scalar, false, scalars: $scalars, constraints: $schema->constraints);
        }
        if (\in_array('array', $types, true)) {
            $alternatives[] = new self(ShapeKind::List, false, items: $schema->items === null ? self::unknown() : self::of($schema->items), constraints: $schema->constraints);
        }
        if (\in_array('object', $types, true)) {
            $alternatives[] = new self(ShapeKind::Object, false, members: self::members($schema));
        }

        return match (\count($alternatives)) {
            0 => null,
            1 => $alternatives[0],
            default => new self(ShapeKind::Choice, false, alternatives: $alternatives),
        };
    }

    /**
     * One shape from the branches of a `oneOf` or `anyOf` that each take JSON types no other
     * branch takes, so a value's type picks its branch. Null when two branches share a type, or
     * a branch is `mixed` or a tagged union, since nothing could then say which branch a value is.
     *
     * @param list<Shape> $shapes
     */
    private static function choice(array $shapes): ?self
    {
        $alternatives = [];
        $taken = [];
        $nullable = false;
        foreach ($shapes as $shape) {
            $nullable = $nullable || $shape->nullable;
            foreach ($shape->kind === ShapeKind::Choice ? $shape->alternatives : [$shape] as $alternative) {
                $types = $alternative->jsonTypes();
                if ($types === null || \array_intersect($types, $taken) !== []) {
                    return null;
                }
                $taken = [...$taken, ...$types];
                $alternatives[] = $alternative->nullable(false);
            }
        }
        if (\count($alternatives) < 2) {
            return null;
        }

        // A list is tested before an object, so an empty array reads as the list.
        $rank = static fn(Shape $alternative): int => match ($alternative->kind) {
            ShapeKind::List => 1,
            ShapeKind::Object => 2,
            default => 0,
        };
        \usort($alternatives, static fn(Shape $a, Shape $b): int => $rank($a) <=> $rank($b));

        return new self(ShapeKind::Choice, $nullable, alternatives: $alternatives);
    }

    /** @param list<Shape> $alternatives */
    private function choosing(array $alternatives): self
    {
        return new self(ShapeKind::Choice, $this->nullable, alternatives: $alternatives);
    }

    /** The PHPDoc type of a value of one JSON scalar type under `$constraints`. */
    private static function scalarDoc(string $json, Constraints $constraints): string
    {
        return \in_array($json, ['integer', 'string'], true) ? Narrowing::scalar($json, $constraints) : self::scalarType($json);
    }

    /** @param list<string> $types */
    private static function union(array $types): string
    {
        return \implode('|', \array_values(\array_unique($types)));
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
     * `const`, or, with no discriminator, when every branch is an object requiring a member no
     * other branch declares. Null when the union is tagged neither way.
     *
     * @param list<ValueSchema> $branches
     */
    private static function tagged(?string $discriminator, array $branches): ?self
    {
        if ($branches === []) {
            return null;
        }
        if ($discriminator === null) {
            return self::signed($branches);
        }

        $out = [];
        foreach ($branches as $branch) {
            $value = ($branch->properties[$discriminator] ?? null)?->const;
            $shape = self::of($branch);
            if ($value === null || $shape->kind !== ShapeKind::Object) {
                return null;
            }
            $out[] = ['value' => $value, 'shape' => $shape, 'signature' => []];
        }

        return new self(ShapeKind::Tagged, false, discriminator: $discriminator, branches: $out);
    }

    /**
     * An undiscriminated union whose branches a value tells apart: each branch is an object that
     * requires at least one member no other branch declares, and that signature selects it. A
     * branch is named by its `title`, or by its position when it has none. Null when a branch is
     * not an object or has no signature, since nothing could then say which branch a value is.
     *
     * @param list<ValueSchema> $branches
     */
    private static function signed(array $branches): ?self
    {
        if (\count($branches) < 2) {
            return null;
        }

        $shapes = [];
        $declaredBy = [];
        foreach ($branches as $index => $branch) {
            $shape = self::of($branch);
            if ($shape->kind !== ShapeKind::Object || $shape->nullable) {
                return null;
            }
            $shapes[$index] = $shape;
            foreach (\array_keys($shape->members) as $name) {
                $declaredBy[$name][] = $index;
            }
        }

        $out = [];
        $names = [];
        foreach ($shapes as $index => $shape) {
            $signature = [];
            foreach ($shape->members as $name => $member) {
                if ($member['required'] && \count($declaredBy[$name]) === 1) {
                    $signature[] = (string) $name;
                }
            }
            $value = $branches[$index]->title ?? 'variant' . ($index + 1);
            if ($signature === [] || isset($names[\strtolower($value)])) {
                return null;
            }
            $names[\strtolower($value)] = true;
            $out[] = ['value' => $value, 'shape' => $shape, 'signature' => $signature];
        }

        return new self(ShapeKind::Tagged, false, branches: $out);
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
