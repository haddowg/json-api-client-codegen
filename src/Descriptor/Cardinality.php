<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

enum Cardinality: string
{
    case One = 'one';
    case Many = 'many';
}
