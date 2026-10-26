<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\Witness;

/**
 * @generated from `components.schemas.Priority`
 */
enum Priority: int
{
    case Low = 1;

    case High = 2;
}

/**
 * @generated from `components.schemas.Status`
 */
enum Status: string
{
    /** Not yet visible to readers */
    case Draft = 'draft';

    /** Live and public */
    case Published = 'published';

    /**  */
    case Archived = 'archived';
}
