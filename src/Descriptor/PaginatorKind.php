<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The paginator an endpoint advertises: the `x-paginator` strategy its page schema is stamped
 * with, or the one its `page[…]` member names imply where it carries no stamp.
 *
 * `None` is what makes `_page()` absent rather than null on a type whose collection is not
 * paginated. `Fixed` is paginated but has no size control: the server fixes the page size and
 * advertises only the page selector, so it gets the whole pagination surface except the size
 * parameter. `Multi` is a menu of the others, one of which the client picks per request.
 */
enum PaginatorKind: string
{
    case Page = 'page';
    case Fixed = 'fixed';
    case Offset = 'offset';
    case Cursor = 'cursor';
    case Multi = 'multi';
    case None = 'none';
}
