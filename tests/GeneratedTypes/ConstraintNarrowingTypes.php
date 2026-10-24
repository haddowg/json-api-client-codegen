<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiClient\Tests\Generated\MusicCatalog\Album;
use haddowg\JsonApiClient\Tests\Generated\MusicCatalog\Release;
use haddowg\JsonApiClient\Tests\Generated\MusicCatalog\ReleaseFormatVinyl;
use haddowg\JsonApiClient\Tests\Generated\MusicCatalog\Track;
use haddowg\JsonApiClient\Tests\Generated\MusicCatalog\TrackCreate;

use function PHPStan\Testing\assertType;

/**
 * The types the music-catalog golden output narrows from its document's constraints, asserted at
 * level 9.
 *
 * Analysed by `composer phpstan-generated` and never executed. Each read is typed from the read
 * schema and each write from its own request schema, nullability included, so a narrowing that
 * the generated code only claims and PHPStan does not see fails here.
 */
final class ConstraintNarrowingTypes
{
    public function readsAreNarrowedAndKeepExactlyTheNullTheSchemaAdmits(Track $track, Album $album): void
    {
        assertType('non-empty-string', $track->title());
        assertType('non-empty-string', $track->title);
        assertType('int<1, max>|null', $track->trackNumber());
        assertType('int<1, max>|null', $track->trackNumber);
        assertType('non-empty-list<non-empty-string>|null', $track->genres());
        assertType('int|null', $track->durationSeconds());
        assertType('haddowg\JsonApiClient\Tests\Generated\MusicCatalog\AlbumStatus|null', $album->status());
    }

    public function nestedMembersAreNarrowedToo(Release $release): void
    {
        $format = $release->format();
        if ($format instanceof ReleaseFormatVinyl) {
            assertType('int<16, 78>|null', $format->rpm);
        }

        assertType('array{material?: non-empty-string, gatefold?: bool|null}|null', $release->packaging());
    }

    public function writesTakeTheNarrowedTypes(TrackCreate $create): void
    {
        assertType('non-empty-string', $create->title);
        assertType('haddowg\JsonApiClient\Support\Missing|int<1, max>', $create->trackNumber);
        assertType('haddowg\JsonApiClient\Support\Missing|non-empty-list<non-empty-string>', $create->genres);
    }
}
