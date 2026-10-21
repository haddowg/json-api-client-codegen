<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\ClientOptions;
use haddowg\JsonApiClient\Http\Transport;
use haddowg\JsonApiClient\Resources\Fetcher;

/**
 * The API's root client: one accessor per resource type, each built over the same transport, so one
 * client reaches the whole API.
 *
 * @generated from the OpenAPI document's resource types
 */
final class MusicCatalogClient
{
    public readonly Albums $albums;
    public readonly Artists $artists;
    public readonly CatalogExports $catalogExports;
    public readonly Charts $charts;
    public readonly Countries $countries;
    public readonly Devices $devices;
    public readonly ExportJobs $exportJobs;
    public readonly Favorites $favorites;
    public readonly Genres $genres;
    public readonly Libraries $libraries;
    public readonly Playlists $playlists;
    public readonly Products $products;
    public readonly PublicProfiles $publicProfiles;
    public readonly Releases $releases;
    public readonly Tracks $tracks;

    public function __construct(ClientOptions $options)
    {
        $fetcher = new Fetcher(new Transport($options));

        $this->albums = new Albums($fetcher);
        $this->artists = new Artists($fetcher);
        $this->catalogExports = new CatalogExports($fetcher);
        $this->charts = new Charts($fetcher);
        $this->countries = new Countries($fetcher);
        $this->devices = new Devices($fetcher);
        $this->exportJobs = new ExportJobs($fetcher);
        $this->favorites = new Favorites($fetcher);
        $this->genres = new Genres($fetcher);
        $this->libraries = new Libraries($fetcher);
        $this->playlists = new Playlists($fetcher);
        $this->products = new Products($fetcher);
        $this->publicProfiles = new PublicProfiles($fetcher);
        $this->releases = new Releases($fetcher);
        $this->tracks = new Tracks($fetcher);
    }
}
