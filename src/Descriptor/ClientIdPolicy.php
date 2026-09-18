<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * Whether a create accepts a client-generated id: no parameter at all, `?string $id = null`,
 * or a required one.
 */
enum ClientIdPolicy: string
{
    case Forbidden = 'forbidden';
    case Optional = 'optional';
    case Required = 'required';
}
