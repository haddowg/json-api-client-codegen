<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Spec;

/**
 * A structure the codegen needs is absent from the document, or is not the shape it must be.
 *
 * Every message names the JSON pointer it was reading, so a document this codegen cannot read
 * reports `expected components.schemas.AlbumsResource.properties.type.const` rather than a
 * TypeError raised somewhere downstream.
 */
final class SpecException extends \RuntimeException
{
    public static function missing(string $pointer): self
    {
        return new self('expected ' . self::label($pointer));
    }

    public static function wrongType(string $pointer, string $expected, mixed $actual): self
    {
        return new self(\sprintf(
            'expected %s to be %s, got %s',
            self::label($pointer),
            $expected,
            self::describe($actual),
        ));
    }

    /**
     * A structure that is present and well-typed but says something the codegen cannot act on,
     * such as a relationship whose linkage matches none of the shapes the projector emits.
     */
    public static function malformed(string $pointer, string $detail): self
    {
        return new self(\sprintf('expected %s %s', self::label($pointer), $detail));
    }

    public static function unreadable(string $source, string $detail): self
    {
        return new self(\sprintf('could not read %s: %s', $source, $detail));
    }

    private static function label(string $pointer): string
    {
        return $pointer === '' ? 'the document root' : $pointer;
    }

    /** The JSON type name of a decoded value, for the "got …" half of a message. */
    private static function describe(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            \is_bool($value) => 'a boolean',
            \is_int($value), \is_float($value) => 'a number',
            \is_string($value) => 'a string',
            \is_array($value) => $value === [] || \array_is_list($value) ? 'an array' : 'an object',
            default => \get_debug_type($value),
        };
    }
}
