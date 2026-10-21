<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * What a generated declaration is. The order of the cases is the order declarations appear in a
 * file, so a parent is always declared before the child that names it.
 */
enum UnitKind: int
{
    case Interface = 0;
    case Trait = 1;
    case Enum = 2;
    case AbstractClass = 3;
    case FinalClass = 4;
}
