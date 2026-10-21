<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\ClientOptions;
use haddowg\JsonApiClient\Http\Transport;
use haddowg\JsonApiClient\Resources\Fetcher;

/**
 * The Music Catalog API, one accessor per resource type.
 *
 * Trimmed to `albums` here. A full run carries one property per type in the document, each
 * built over the same transport, so one client reaches the whole API.
 *
 * `atomic()` is absent from this artefact even though `/operations` advertises the extension —
 * see the README. When it is generated it is generated *because* the server advertises the
 * atomic media type, and on a server that does not it is simply not there.
 *
 * @generated from the Music Catalog API OpenAPI 3.1 document
 */
final class MusicCatalogClient
{
    public readonly Albums $albums;

    public function __construct(ClientOptions $options)
    {
        $fetcher = new Fetcher(new Transport($options));

        $this->albums = new Albums($fetcher);
    }
}
