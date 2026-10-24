<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

use haddowg\JsonApiCodegen\Descriptor\ValueSchema;
use haddowg\JsonApiCodegen\Emit\Narrowing;

/**
 * A JSON Schema fragment as a PHPStan type and the native type that holds it.
 *
 * Composition follows the keyword's own meaning: `allOf` merges its branches, `anyOf` with one
 * non-null branch is that branch and with several is one shape whose members are all optional
 * (the branches are not exclusive), `oneOf` is a union of its branches, and a `{"type": "null"}`
 * branch is nullability rather than a branch.
 *
 * The doc type is narrowed by the schema's constraints (see {@see Narrowing}); the native type
 * never is, so a value outside a bound is a PHPStan error and not a runtime one.
 */
final class Shape
{
    private function __construct(
        public readonly string $doc,
        public readonly string $native,
    ) {}

    public static function of(ValueSchema $schema): self
    {
        $fields = self::fields($schema);
        if ($fields !== null) {
            return new self(self::objectShape($fields), 'array');
        }

        if ($schema->oneOf !== [] || $schema->anyOf !== []) {
            $branches = \array_map(self::of(...), self::nonNull([...$schema->oneOf, ...$schema->anyOf]));
            if ($branches === []) {
                return new self('mixed', 'mixed');
            }
            $natives = \array_unique(\array_map(static fn(self $branch): string => $branch->native, $branches));

            return new self(
                \implode('|', \array_unique(\array_map(static fn(self $branch): string => $branch->doc, $branches))),
                \count($natives) === 1 ? $natives[0] : 'mixed',
            );
        }

        return match (self::jsonType($schema)) {
            'array' => new self(Narrowing::list($schema->items === null ? 'mixed' : self::nullable($schema->items), $schema->constraints), 'array'),
            'object' => new self('array<string, mixed>', 'array'),
            'string' => new self(Narrowing::scalar('string', $schema->constraints), 'string'),
            'integer' => new self(Narrowing::scalar('integer', $schema->constraints), 'int'),
            'number' => new self('float', 'float'),
            'boolean' => new self('bool', 'bool'),
            default => new self('mixed', 'mixed'),
        };
    }

    /** True when the schema admits null, on its own types or through a null branch. */
    public static function admitsNull(ValueSchema $schema): bool
    {
        if (\in_array('null', $schema->types, true)) {
            return true;
        }

        foreach ([...$schema->oneOf, ...$schema->anyOf] as $branch) {
            if ($branch->types === ['null']) {
                return true;
            }
        }

        return false;
    }

    /** The doc type, with `|null` when the schema admits null. */
    private static function nullable(ValueSchema $schema): string
    {
        $doc = self::of($schema)->doc;

        return $doc !== 'mixed' && self::admitsNull($schema) ? $doc . '|null' : $doc;
    }

    /**
     * The members of an object-like schema, in document order, or null when it is not one.
     *
     * @return array<string, array{schema: ValueSchema, required: bool}>|null
     */
    private static function fields(ValueSchema $schema): ?array
    {
        $own = null;
        if ($schema->properties !== [] || (self::jsonType($schema) === 'object' && !$schema->composed())) {
            if ($schema->properties === []) {
                return null;
            }
            $own = [];
            foreach ($schema->properties as $name => $property) {
                $own[$name] = ['schema' => $property, 'required' => \in_array($name, $schema->required, true)];
            }
        }

        if (!$schema->composed()) {
            return $own;
        }
        if ($schema->oneOf !== []) {
            return null;
        }

        $merged = $own ?? [];
        foreach ($schema->allOf as $branch) {
            $fields = self::fields($branch);
            if ($fields === null) {
                return null;
            }
            $merged = self::merge($merged, $fields, true);
        }

        $any = self::nonNull($schema->anyOf);
        foreach ($any as $branch) {
            $fields = self::fields($branch);
            if ($fields === null) {
                return null;
            }
            $merged = self::merge($merged, $fields, \count($any) === 1);
        }

        return $merged === [] ? null : $merged;
    }

    /**
     * @param array<string, array{schema: ValueSchema, required: bool}> $into
     * @param array<string, array{schema: ValueSchema, required: bool}> $fields
     *
     * @return array<string, array{schema: ValueSchema, required: bool}>
     */
    private static function merge(array $into, array $fields, bool $keepRequired): array
    {
        foreach ($fields as $name => $field) {
            $required = $keepRequired && $field['required'];
            $into[$name] = isset($into[$name])
                ? ['schema' => $into[$name]['schema'], 'required' => $into[$name]['required'] || $required]
                : ['schema' => $field['schema'], 'required' => $required];
        }

        return $into;
    }

    /**
     * @param array<string, array{schema: ValueSchema, required: bool}> $fields
     */
    private static function objectShape(array $fields): string
    {
        $members = [];
        foreach ($fields as $name => $field) {
            $key = \preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) === 1 ? $name : "'" . \str_replace("'", "\\'", $name) . "'";
            $members[] = $key . ($field['required'] ? '' : '?') . ': ' . self::nullable($field['schema']);
        }

        return 'array{' . \implode(', ', $members) . '}';
    }

    /**
     * @param list<ValueSchema> $branches
     *
     * @return list<ValueSchema>
     */
    private static function nonNull(array $branches): array
    {
        return \array_values(\array_filter($branches, static fn(ValueSchema $branch): bool => $branch->types !== ['null']));
    }

    private static function jsonType(ValueSchema $schema): ?string
    {
        foreach ($schema->types as $type) {
            if ($type !== 'null') {
                return $type;
            }
        }

        return null;
    }
}
