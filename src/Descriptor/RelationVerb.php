<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * A mutation the relationship endpoint advertises for one relation.
 *
 * To-many: `add` from POST, `remove` from DELETE, `replace` from PATCH. To-one: `set` from
 * PATCH. These are not uniform across relations, which is why a to-one has `set()` and no
 * `add()` statically rather than a runtime guard.
 */
enum RelationVerb: string
{
    case Add = 'add';
    case Remove = 'remove';
    case Replace = 'replace';
    case Set = 'set';
}
