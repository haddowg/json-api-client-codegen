<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

use haddowg\JsonApiCodegen\Descriptor\Constraints;

/**
 * Turns a schema's constraints into the narrowest PHPStan type that holds them, and the rest into
 * prose.
 *
 * Only what a PHPStan type states exactly is narrowed: integer bounds become `int<min, max>`
 * (`positive-int` where it says the same thing, as the paging parameters always have), a `minLength` of at
 * least 1 becomes `non-empty-string`, and a `minItems` of at least 1 becomes `non-empty-list`.
 * Every other keyword is written as a sentence for the reader, because a type that claimed it
 * would be a type the generated code cannot honour. Nothing here is checked at runtime: contract 5
 * projects a constraint only where every value satisfies it, so the narrowed type
 * is the server's guarantee rather than the client's check.
 */
final class Narrowing
{
    /**
     * Format hints that need no sentence: the dates are coerced into `\DateTimeImmutable`, and
     * the rest are the JSON type a descriptor falls back to when a schema declares no format.
     */
    private const array SILENT_FORMATS = ['date', 'date-time', 'time', 'string', 'integer', 'number', 'boolean', 'object', 'array', 'unknown'];

    /**
     * The PHPDoc type of a scalar of the given JSON type under its constraints.
     */
    public static function scalar(string $json, Constraints $constraints): string
    {
        return match ($json) {
            'integer' => self::integer($constraints),
            'string' => $constraints->minLength !== null && $constraints->minLength >= 1 ? 'non-empty-string' : 'string',
            'number' => 'float',
            'boolean' => 'bool',
            default => 'mixed',
        };
    }

    /**
     * A list of `$item` under the list's own constraints.
     */
    public static function list(string $item, Constraints $constraints): string
    {
        return ($constraints->minItems !== null && $constraints->minItems >= 1 ? 'non-empty-list<' : 'list<') . $item . '>';
    }

    /**
     * An integer's type from its bounds. An exclusive bound narrows too, since the integer just
     * inside it is exact.
     */
    public static function integer(Constraints $constraints): string
    {
        $low = self::tighter(
            $constraints->minimum === null ? null : (int) \ceil($constraints->minimum),
            $constraints->exclusiveMinimum === null ? null : (int) \floor($constraints->exclusiveMinimum) + 1,
            true,
        );
        $high = self::tighter(
            $constraints->maximum === null ? null : (int) \floor($constraints->maximum),
            $constraints->exclusiveMaximum === null ? null : (int) \ceil($constraints->exclusiveMaximum) - 1,
            false,
        );

        return match (true) {
            $low === null && $high === null => 'int',
            $high === null && $low === 1 => 'positive-int',
            default => 'int<' . ($low ?? 'min') . ', ' . ($high ?? 'max') . '>',
        };
    }

    /**
     * The constraints no type of a value of `$json` expresses, as one sentence, or null when the
     * type says everything.
     *
     * @param bool $withDefault whether to state the `default`, which only means something on a
     *                          parameter the server fills in when it is left out
     */
    public static function prose(string $json, Constraints $constraints, ?string $format = null, bool $withDefault = false): ?string
    {
        $notes = [];

        if ($json === 'number') {
            $notes[] = self::range(
                $constraints->exclusiveMinimum === null ? self::bound('at least', $constraints->minimum) : self::bound('greater than', $constraints->exclusiveMinimum),
                $constraints->exclusiveMaximum === null ? self::bound('at most', $constraints->maximum) : self::bound('less than', $constraints->exclusiveMaximum),
            );
        }
        if (\in_array($json, ['integer', 'number'], true) && $constraints->multipleOf !== null) {
            $notes[] = 'a multiple of ' . self::number($constraints->multipleOf);
        }

        if ($json === 'string') {
            $notes[] = self::count(
                $constraints->minLength !== null && $constraints->minLength > 1 ? $constraints->minLength : null,
                $constraints->maxLength,
                'character',
            );
            if ($constraints->pattern !== null) {
                $notes[] = 'matches `' . $constraints->pattern . '`';
            }
            if ($format !== null && !\in_array($format, self::SILENT_FORMATS, true)) {
                $notes[] = 'in `' . $format . '` format';
            }
        }

        if ($json === 'array') {
            $notes[] = self::count(
                $constraints->minItems !== null && $constraints->minItems > 1 ? $constraints->minItems : null,
                $constraints->maxItems,
                'item',
            );
            if ($constraints->uniqueItems) {
                $notes[] = 'no duplicate items';
            }
        }

        if ($json === 'object') {
            $notes[] = self::count($constraints->minProperties, $constraints->maxProperties, 'member');
        }

        if ($withDefault && $constraints->default !== null) {
            $notes[] = 'defaults to ' . self::literal($constraints->default);
        }

        $notes = \array_values(\array_filter($notes, static fn(?string $note): bool => $note !== null));
        if ($notes === []) {
            return null;
        }

        return \ucfirst(\implode('; ', $notes)) . '.';
    }

    private static function tighter(?int $a, ?int $b, bool $lower): ?int
    {
        if ($a === null || $b === null) {
            return $a ?? $b;
        }

        return $lower ? \max($a, $b) : \min($a, $b);
    }

    private static function bound(string $words, int|float|null $value): ?string
    {
        return $value === null ? null : $words . ' ' . self::number($value);
    }

    private static function range(?string $low, ?string $high): ?string
    {
        return $low === null ? $high : ($high === null ? $low : $low . ' and ' . $high);
    }

    private static function count(?int $min, ?int $max, string $noun): ?string
    {
        return match (true) {
            $min !== null && $max !== null => 'between ' . $min . ' and ' . $max . ' ' . $noun . 's',
            $min !== null => 'at least ' . $min . ' ' . $noun . ($min === 1 ? '' : 's'),
            $max !== null => 'at most ' . $max . ' ' . $noun . ($max === 1 ? '' : 's'),
            default => null,
        };
    }

    private static function number(int|float $value): string
    {
        return \is_int($value) ? (string) $value : \rtrim(\rtrim(\sprintf('%.10F', $value), '0'), '.');
    }

    private static function literal(int|float|string|bool $value): string
    {
        return match (true) {
            \is_bool($value) => $value ? '`true`' : '`false`',
            \is_string($value) => '`' . $value . '`',
            default => self::number($value),
        };
    }
}
