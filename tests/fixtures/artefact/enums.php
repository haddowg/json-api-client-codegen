<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

/**
 * Where the album sits in its release lifecycle.
 *
 * Case names come from `x-enum-varnames` and the per-case documentation from
 * `x-enum-descriptions`, both of which the projector emits alongside the `enum` member.
 *
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
