<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * How a raw action body is read or written: as opaque bytes, as text, or as JSON with a schema.
 */
enum RawMediaKind: string
{
    case Binary = 'binary';
    case Text = 'text';
    case Json = 'json';
}
