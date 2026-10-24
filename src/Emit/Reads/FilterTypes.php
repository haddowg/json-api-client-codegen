<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\ValueSchema;
use haddowg\JsonApiCodegen\Emit\Narrowing;

/**
 * Turns a filter's value schema into the PHP types its builder methods take.
 *
 * Values follow the same coercions as attributes: a `date`, `date-time` or `time` bound accepts a
 * `\DateTimeImmutable` or an ISO string, `number` is `float` and `integer` is `int`. A schema the
 * document leaves empty, or one built only from composition keywords, is `mixed`: the filter's
 * value type is genuinely unknown, and guessing it from the field it filters would be inventing
 * a contract the document does not state. A filter schema's constraints narrow the doc type the
 * way they narrow an attribute's (see {@see Narrowing}).
 */
final class FilterTypes
{
    private const array DATE_FORMATS = ['date', 'date-time', 'time'];

    /**
     * @param string $dateInput the type a date bound accepts: the resource's date input alias
     *                          where one is declared, else the union it stands for
     */
    public function __construct(
        private readonly string $dateInput,
        private readonly bool $dateInputIsAlias,
    ) {}

    /** True for a value that is a map of named members, declared as an array shape. */
    public static function structured(?ValueSchema $schema): bool
    {
        return $schema !== null
            && !$schema->composed()
            && $schema->properties !== []
            && \in_array('object', $schema->types, true);
    }

    /** True for a `{min, max}` pair of one scalar type, which is how every range filter projects. */
    public static function range(ValueSchema $schema): bool
    {
        if (!self::structured($schema) || $schema->required !== []) {
            return false;
        }
        $names = \array_keys($schema->properties);
        \sort($names, \SORT_STRING);
        if ($names !== ['max', 'min']) {
            return false;
        }

        $min = $schema->properties['min'];
        $max = $schema->properties['max'];

        return $min->types === $max->types && $min->format === $max->format && !$min->composed() && $min->properties === [];
    }

    /** True for a range whose bounds are dates or times. */
    public static function dateRange(ValueSchema $schema): bool
    {
        return self::range($schema) && \in_array($schema->properties['min']->format, self::DATE_FORMATS, true);
    }

    /**
     * The member names of a structured value, in the order its shape declares them. A range reads
     * low to high, `min` then `max`; any other shape keeps the descriptor's order.
     *
     * @return list<string>
     */
    public static function members(ValueSchema $schema): array
    {
        return self::range($schema) ? ['min', 'max'] : \array_map('strval', \array_keys($schema->properties));
    }

    public function type(?ValueSchema $schema): ValueType
    {
        if ($schema === null || $schema->composed()) {
            return ValueType::mixed();
        }

        $nullable = \in_array('null', $schema->types, true);
        $types = \array_values(\array_filter($schema->types, static fn(string $type): bool => $type !== 'null'));
        if ($types === []) {
            return ValueType::mixed();
        }

        $docs = [];
        $natives = [];
        $aliases = [];
        foreach ($types as $type) {
            $one = $this->single($type, $schema);
            $docs[] = $one->doc;
            foreach (\explode('|', $one->native) as $native) {
                $natives[$native] = true;
            }
            \array_push($aliases, ...$one->aliases);
        }

        if (\in_array('mixed', $docs, true)) {
            return ValueType::mixed();
        }

        $natives = \array_keys($natives);
        if ($nullable) {
            $docs[] = 'null';
        }

        $native = \implode('|', $natives);
        if ($nullable) {
            $native = \count($natives) === 1 ? '?' . $native : $native . '|null';
        }

        return new ValueType(\implode('|', $docs), $native, \array_values(\array_unique($aliases)));
    }

    /**
     * A structured value's array shape, members typed recursively.
     *
     * @return array{string, list<string>} the shape and the aliases it names
     */
    public function shape(ValueSchema $schema): array
    {
        $parts = [];
        $aliases = [];
        foreach (self::members($schema) as $member) {
            $type = $this->type($schema->properties[$member]);
            $optional = \in_array($member, $schema->required, true) ? '' : '?';
            $parts[] = self::key($member) . $optional . ': ' . $type->doc;
            \array_push($aliases, ...$type->aliases);
        }

        return ['array{' . \implode(', ', $parts) . '}', \array_values(\array_unique($aliases))];
    }

    /** An array-shape key, quoted where it is not a bare identifier (`'artist.name'`). */
    public static function key(string $name): string
    {
        return \preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) === 1 ? $name : "'" . \str_replace("'", "\\'", $name) . "'";
    }

    private function single(string $type, ValueSchema $schema): ValueType
    {
        return match ($type) {
            'string' => $this->string($schema),
            'integer' => new ValueType(Narrowing::scalar('integer', $schema->constraints), 'int'),
            'number' => new ValueType('float', 'float'),
            'boolean' => new ValueType('bool', 'bool'),
            'array' => $this->list($schema),
            'object' => $this->object($schema),
            default => ValueType::mixed(),
        };
    }

    private function string(ValueSchema $schema): ValueType
    {
        if (\in_array($schema->format, self::DATE_FORMATS, true)) {
            return new ValueType($this->dateInput, '\DateTimeImmutable|string', $this->dateInputIsAlias ? [$this->dateInput] : []);
        }

        if ($schema->enum !== []) {
            return new ValueType(\implode('|', \array_map(static fn(string $case): string => "'" . \str_replace("'", "\\'", $case) . "'", $schema->enum)), 'string');
        }

        return new ValueType(Narrowing::scalar('string', $schema->constraints), 'string');
    }

    private function list(ValueSchema $schema): ValueType
    {
        $item = $this->type($schema->items);

        return new ValueType(Narrowing::list($item->doc, $schema->constraints), 'array', $item->aliases);
    }

    private function object(ValueSchema $schema): ValueType
    {
        if (!self::structured($schema)) {
            return new ValueType('array<string, mixed>', 'array');
        }

        [$shape, $aliases] = $this->shape($schema);

        return new ValueType($shape, 'array', $aliases);
    }
}
