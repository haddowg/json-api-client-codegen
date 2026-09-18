<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/** Where a custom action is invoked: on the collection, or on one resource. */
enum ActionScope: string
{
    case Collection = 'collection';
    case Resource = 'resource';
}
