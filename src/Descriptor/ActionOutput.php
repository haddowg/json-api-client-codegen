<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/** What a custom action returns: a JSON:API document, a meta-only document, or nothing. */
enum ActionOutput: string
{
    case Document = 'document';
    case Meta = 'meta';
    case None = 'none';
}
