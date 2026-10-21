<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The `source` member a catalogued error code's variant requires.
 */
enum ErrorSourceKind: string
{
    case Pointer = 'pointer';
    case Parameter = 'parameter';
}
