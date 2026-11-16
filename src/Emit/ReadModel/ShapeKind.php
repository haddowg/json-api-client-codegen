<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

enum ShapeKind
{
    /** One or more JSON scalar types: a `type` array of scalars keeps every one. */
    case Scalar;
    case Object;
    case List;
    case Tagged;
    case Mixed;
    /**
     * A value whose JSON type picks one of several shapes: a `type` array mixing scalars with an
     * array or an object, or a `oneOf` or `anyOf` whose branches each take their own JSON types.
     */
    case Choice;
}
