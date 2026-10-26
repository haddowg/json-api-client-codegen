<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiCodegen\Tests\Golden\Witness\WitnessClient;

use function PHPStan\Testing\assertType;

/**
 * The type each raw action of the `witness` golden output returns for the media type it asks
 * for, asserted at level 9.
 *
 * Analysed by `composer phpstan-generated` and never executed: `assertType()` does not exist at
 * runtime. A mismatch is a non-ignorable `phpstan.type` error.
 */
final class RawActionTypes
{
    public function anActionWithAJsonApiResponseAnswersWithItUnlessAskedForAnother(WitnessClient $client): void
    {
        $report = $client->articles->actions();

        assertType('array<string, mixed>', $report->report());
        assertType('array<string, mixed>', $report->report('application/vnd.api+json'));
        assertType('string', $report->report('text/csv'));
        assertType('Psr\Http\Message\StreamInterface', $report->report('application/pdf'));
        assertType('haddowg\JsonApiCodegen\Tests\Golden\Witness\ArticleSummary', $report->report(accept: 'application/json'));
    }

    public function anActionWithOneRawMediaTypeTakesNoChoice(WitnessClient $client): void
    {
        assertType('Psr\Http\Message\StreamInterface', $client->articles->id('art-1')->actions()->cover());
    }

    public function aJsonBodyIsReadIntoItsSchemasClass(WitnessClient $client): void
    {
        $summary = $client->articles->actions()->report('application/json');

        assertType('int<0, max>', $summary->total);
        assertType('array<string, mixed>|null', $summary->byStatus);
    }
}
