<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

enum ShapeKind
{
    case Scalar;
    case Object;
    case List;
    case Tagged;
    case Mixed;
}
