<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

/** How the array door's loose value becomes the typed one. */
enum Coercion
{
    case None;
    case Date;
    case Enum;
}
