<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The `source` member every code of a catalogued error shape fills.
 */
enum ErrorSourceKind: string
{
    case Pointer = 'pointer';
    case Parameter = 'parameter';
    case Header = 'header';
}
