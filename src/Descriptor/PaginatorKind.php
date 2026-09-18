<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The paginator an endpoint advertises, read from its `page[…]` query parameters.
 *
 * `None` is what makes `_page()` absent rather than null on a type whose collection is not
 * paginated.
 */
enum PaginatorKind: string
{
    case Page = 'page';
    case Offset = 'offset';
    case Cursor = 'cursor';
    case None = 'none';
}
