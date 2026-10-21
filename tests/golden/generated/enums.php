<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

/**
 * @generated from `components.schemas.AlbumStatus`
 */
enum AlbumStatus: string
{
    /** Announced but not yet on sale. */
    case Upcoming = 'upcoming';

    /** Released and available to stream or buy. */
    case Released = 'released';

    /** Withdrawn from the catalogue (back-catalogue or rights lapsed). */
    case Withdrawn = 'withdrawn';
}
