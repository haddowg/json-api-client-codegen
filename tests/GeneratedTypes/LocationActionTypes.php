<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiClient\ActionLocation;
use haddowg\JsonApiCodegen\Tests\Golden\Witness\WitnessClient;

use function PHPStan\Testing\assertType;

/**
 * The type each action of the `witness` golden output that answers with a URL returns, asserted at
 * level 9.
 *
 * Analysed by `composer phpstan-generated` and never executed.
 */
final class LocationActionTypes
{
    public function anActionAnsweringOnlyWithAUrlReturnsItsLocation(WitnessClient $client): void
    {
        $location = $client->articles->id('art-1')->actions()->download();

        assertType('haddowg\JsonApiClient\ActionLocation', $location);
        assertType('string', $location->url);
        assertType('string', $location->type);
        assertType('DateTimeImmutable|null', $location->expiresAt);
    }

    public function anActionAnsweringWithADocumentOrAUrlReturnsEither(WitnessClient $client): void
    {
        $revision = $client->articles->id('art-1')->actions()->revision();
        assertType('haddowg\JsonApiClient\ActionLocation|haddowg\JsonApiCodegen\Tests\Golden\Witness\ArticleBase', $revision);

        if (!$revision instanceof ActionLocation) {
            assertType('haddowg\JsonApiCodegen\Tests\Golden\Witness\ArticleBase', $revision);
        }
    }

    public function aBareSeeOtherActionIsCalledForNothing(WitnessClient $client): void
    {
        $client->articles->actions()->archive();
    }
}
