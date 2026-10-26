<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * How a raw action body is read or written: as opaque bytes, as text, as JSON with a schema, or,
 * for a request body only, as form fields the runtime encodes.
 */
enum RawMediaKind: string
{
    case Binary = 'binary';
    case Text = 'text';
    case Json = 'json';
    case Multipart = 'multipart';
    case Form = 'form';

    /** The form kind of a request media type, or null when it is not a form. */
    public static function formOf(string $mediaType): ?self
    {
        return match (\strtolower(\trim(\explode(';', $mediaType, 2)[0]))) {
            'multipart/form-data' => self::Multipart,
            'application/x-www-form-urlencoded' => self::Form,
            default => null,
        };
    }

    /** Whether a body of this kind is a field array the runtime encodes. */
    public function isForm(): bool
    {
        return $this === self::Multipart || $this === self::Form;
    }
}
