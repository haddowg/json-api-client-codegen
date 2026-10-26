<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

use haddowg\JsonApiCodegen\Descriptor\Constraints;
use haddowg\JsonApiCodegen\Emit\Identifier;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;

/**
 * Writes the statements that recover a declared shape from a value the runtime hands back as
 * `mixed`, one guard per member.
 *
 * An optional member of the wrong type is dropped rather than asserted, so a server sending the
 * wrong thing loses that member instead of poisoning the type. A required member of the wrong
 * type throws `MalformedDocument`, because dropping it would make the declared shape a lie.
 *
 * Constraints are asserted, not checked: a narrowed member gets an inline `@var` once its JSON
 * type is known, because the server guarantees the bound and a client that rejected data over it
 * would be rejecting real responses.
 */
final class Guard
{
    /** @var array<string, true> */
    private array $taken;

    /**
     * @param list<string> $reserved local variable names already in use
     */
    public function __construct(private readonly string $where, array $reserved = [])
    {
        $this->taken = \array_fill_keys(['this', ...$reserved], true);
    }

    /**
     * A local variable name, unique within the method being written.
     */
    public function variable(string $base): string
    {
        $base = Identifier::isMemberName($base) ? $base : 'member' . \ucfirst($base);
        $name = $base;
        for ($i = 2; isset($this->taken[\strtolower($name)]); $i++) {
            $name = $base . $i;
        }
        $this->taken[\strtolower($name)] = true;

        return $name;
    }

    /**
     * Extract every member of `$in` into a local and build the shape into `$shape`.
     *
     * @param array<string, array{shape: Shape, required: bool}> $members
     *
     * @return list<string> lines, indented for a method body's first level
     */
    public function object(array $members, string $in, string $shape, string $prefix): array
    {
        $locals = [];
        foreach (\array_keys($members) as $name) {
            $locals[$name] = $this->variable($prefix === '' ? Naming::camel((string) $name) : $prefix . Naming::pascal((string) $name));
        }

        $lines = ['$' . $shape . ' = [];'];
        foreach ($locals as $name => $local) {
            $lines[] = '$' . $local . ' = $' . $in . '[' . Php::string((string) $name) . '] ?? null;';
        }

        foreach ($members as $name => $member) {
            $name = (string) $name;
            $lines[] = '';
            \array_push($lines, ...$this->value(
                $member['shape']->merged(),
                $locals[$name],
                '$' . $shape . '[' . Php::string($name) . ']',
                $member['required'],
                $member['required'] ? null : '\array_key_exists(' . Php::string($name) . ', $' . $in . ')',
                $name,
            ));
        }

        return $lines;
    }

    /**
     * Assign the guarded value of local `$in` to `$out` when it has the declared shape.
     *
     * @param string|null $exists an expression true when the member is present, which tells an
     *                            explicit null from an absent member; null where there is no
     *                            difference (a list item)
     *
     * @return list<string>
     */
    public function value(Shape $shape, string $in, string $out, bool $required, ?string $exists, string $member): array
    {
        $var = '$' . $in;

        if ($shape->kind === ShapeKind::Mixed) {
            return $required || $exists === null
                ? [$out . ' = ' . $var . ';']
                : ['if (' . $exists . ') {', '    ' . $out . ' = ' . $var . ';', '}'];
        }

        if ($shape->nullable) {
            $inner = $this->value($shape->nullable(false), $in, $out, $required, $exists, $member);
            if ($required) {
                return ['if (' . $var . ' === null) {', '    ' . $out . ' = null;', '} else {', ...self::indent($inner), '}'];
            }
            $null = $exists === null ? $var . ' === null' : $var . ' === null && ' . $exists;

            return ['if (' . $null . ') {', '    ' . $out . ' = null;', '}', '', ...$inner];
        }

        [$condition, $expected] = match ($shape->kind) {
            ShapeKind::Scalar => self::scalarCondition((string) $shape->scalar, $var),
            ShapeKind::List => ['\is_array(' . $var . ') && \array_is_list(' . $var . ')', 'a list'],
            default => ['\is_array(' . $var . ')', 'an object'],
        };

        $body = match ($shape->kind) {
            ShapeKind::Scalar => [
                ...($shape->narrowed() ? [Php::comment('@var ' . $shape->wireType() . ' ' . $var)] : []),
                $out . ' = ' . ($shape->scalar === 'number' ? '(float) ' . $var : $var) . ';',
            ],
            ShapeKind::List => $this->list($shape, $in, $out),
            default => $this->nested($shape, $in, $out),
        };

        if ($required) {
            return [
                'if (' . self::negate($condition) . ') {',
                '    throw MalformedDocument::unexpectedMember(' . Php::string($this->where) . ', ' . Php::string($member) . ', ' . Php::string($expected) . ');',
                '}',
                '',
                ...$body,
            ];
        }

        return ['if (' . $condition . ') {', ...self::indent($body), '}'];
    }

    /** @return list<string> */
    private function nested(Shape $shape, string $in, string $out): array
    {
        if ($shape->members === []) {
            // A decoded JSON object can carry integer-like keys, which PHP turns into ints.
            $key = $this->variable($in . 'Key');
            $item = $this->variable($in . 'Item');

            return [
                $out . ' = [];',
                'foreach ($' . $in . ' as $' . $key . ' => $' . $item . ') {',
                '    ' . $out . '[(string) $' . $key . '] = $' . $item . ';',
                '}',
            ];
        }
        $local = $this->variable($in . 'Shape');

        return [...$this->object($shape->members, $in, $local, $in), '', $out . ' = $' . $local . ';'];
    }

    /** @return list<string> */
    private function list(Shape $shape, string $in, string $out): array
    {
        $list = $this->variable($in . 'List');
        $item = $this->variable($in . 'Item');
        $items = ($shape->items ?? Shape::unknown())->merged();

        return [
            '$' . $list . ' = [];',
            '',
            'foreach ($' . $in . ' as $' . $item . ') {',
            ...self::indent($this->value($items, $item, '$' . $list . '[]', false, null, $item)),
            '}',
            '',
            ...(self::narrowsItself($shape) ? [Php::comment('@var ' . $shape->wireType() . ' $' . $list)] : []),
            $out . ' = $' . $list . ';',
        ];
    }

    /** Whether a shape's own constraints narrow it, as opposed to those of its items. */
    private static function narrowsItself(Shape $shape): bool
    {
        return $shape->wireType() !== $shape->constrained(Constraints::none())->wireType();
    }

    /** @return array{string, string} the condition, and the expected kind for a failure message */
    private static function scalarCondition(string $json, string $var): array
    {
        return match ($json) {
            'integer' => ['\is_int(' . $var . ')', 'an integer'],
            'number' => ['\is_int(' . $var . ') || \is_float(' . $var . ')', 'a number'],
            'boolean' => ['\is_bool(' . $var . ')', 'a boolean'],
            default => ['\is_string(' . $var . ')', 'a string'],
        };
    }

    private static function negate(string $condition): string
    {
        return \preg_match('/^\\\\[a-z_]+\(\$[A-Za-z0-9_]+\)$/', $condition) === 1 ? '!' . $condition : '!(' . $condition . ')';
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    public static function indent(array $lines, int $by = 4): array
    {
        $pad = \str_repeat(' ', $by);

        return \array_map(static fn(string $line): string => $line === '' ? '' : $pad . $line, $lines);
    }
}
