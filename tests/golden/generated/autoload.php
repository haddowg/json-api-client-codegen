<?php

declare(strict_types=1);

/*
 * Loads the generated MusicCatalog client. Classes share a file, one file per resource type,
 * so this map is how each one is found. Register it once through composer.json's
 * `autoload.files`; regenerating rewrites it, so a new type never needs a `dump-autoload`.
 *
 * @generated from an OpenAPI document with sha256 290cf58c0329bc5e7a5a8e520c7fd2697cab62a4cc44ee51fc075de9643803d4
 */

\spl_autoload_register(static function (string $class): void {
    static $map = [
        'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog\\AlbumStatus' => 'enums.php',
    ];

    if (isset($map[$class])) {
        require_once __DIR__ . '/' . $map[$class];
    }
});
