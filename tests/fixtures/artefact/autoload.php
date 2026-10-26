<?php

declare(strict_types=1);

/*
 * Loads the generated Music Catalog client. Classes share a file, one file per resource type,
 * so this map is how each one is found. Register it once through composer.json's
 * `autoload.files`; regenerating rewrites it, so a new type never needs a `dump-autoload`.
 *
 * @generated
 */

\spl_autoload_register(static function (string $class): void {
    static $map = [
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\Album' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumActions' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumArtistRelation' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumBase' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumCatalogueStats' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumCollectionActions' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumCreate' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumCreateBuilder' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumHandle' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumHasArtist' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumHasTracks' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumProjecting' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumProjection' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumQuery' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumStatus' => 'enums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumTracksRelation' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumUpdate' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\Albums' => 'resource-albums.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\Artist' => 'resource-artists.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\ArtistBase' => 'resource-artists.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\ArtistHasAlbums' => 'resource-artists.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\MusicCatalogClient' => 'client.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\Track' => 'resource-tracks.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\TrackBase' => 'resource-tracks.php',
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\TrackHasAlbum' => 'resource-tracks.php',
    ];

    if (isset($map[$class])) {
        require_once __DIR__ . '/' . $map[$class];
    }
});
