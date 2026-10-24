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
        assertType('int<1, max>', $track->trackNumber());
        assertType('int<1, max>', $track->trackNumber);
        assertType('non-empty-list<non-empty-string>', $track->genres());
        assertType('int', $track->durationSeconds());
        assertType('string|null', $track->previewOffset());
        assertType('haddowg\JsonApiClient\Tests\Generated\MusicCatalog\AlbumStatus', $album->status());
    }

    public function nestedMembersAreNarrowedAndRequiredWhereTheServerRendersThem(Release $release, Album $album): void
    {
        $format = $release->format();
        if ($format instanceof ReleaseFormatVinyl) {
            assertType('int<16, 78>', $format->rpm);
            assertType('bool|null', $format->coloured);
        }

        assertType('array{material: non-empty-string, gatefold: bool|null}|null', $release->packaging());
        // A hook renders `releaseInfo`, so none of its members is listed as required.
        assertType('array{label?: string|null, catalogueNumber?: string|null}|null', $album->releaseInfo());
    }

    public function writesTakeTheNarrowedTypes(TrackCreate $create): void
    {
        assertType('non-empty-string', $create->title);
        assertType('int<1, max>', $create->trackNumber);
        assertType('non-empty-list<non-empty-string>', $create->genres);
        assertType('haddowg\JsonApiClient\Support\Missing|int', $create->durationSeconds);
    }
}
