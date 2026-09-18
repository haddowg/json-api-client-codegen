<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/** The body a custom action accepts: a JSON:API document, nothing, or a raw payload. */
enum ActionInput: string
{
    case Document = 'document';
    case None = 'none';
    case Raw = 'raw';
}
