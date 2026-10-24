<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Resources\Pivot;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

/**
 * Runs the generated pivot reads, from the committed golden output, against a playlist response.
 *
 * The classes are reached by name because the generated namespace is not on this package's
 * autoloader. The golden output targets PHP 8.4, hence the requirement.
 */
#[CoversClass(ReadModelEmitter::class)]
#[RequiresPhp('>= 8.4.0')]
final class PivotReadBehaviourTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once \dirname(__DIR__) . '/golden/generated/autoload.php';
    }

    public function testEachLinkageMembersPivotIsReadInLinkageOrder(): void
    {
        $pivots = self::pivots([
            ['type' => 'tracks', 'id' => '4', 'meta' => ['pivot' => ['position' => 1, 'weight' => null, 'addedAt' => '2026-01-02T03:04:05+00:00']]],
            ['type' => 'tracks', 'id' => '9', 'meta' => ['pivot' => ['position' => 2, 'weight' => 5, 'addedAt' => '2026-01-03T00:00:00+00:00']]],
        ]);

        self::assertCount(2, $pivots);
        self::assertSame(Generated::NAMESPACE . '\\PlaylistOrderedTracksPivot', $pivots[0]::class);
        self::assertSame('4', $pivots[0]->_identifier()->id);
        self::assertSame(1, self::read($pivots[0], 'position'));
        self::assertNull(self::read($pivots[0], 'weight'));
        self::assertEquals(new \DateTimeImmutable('2026-01-02T03:04:05+00:00'), self::read($pivots[0], 'addedAt'));
        self::assertSame('9', $pivots[1]->_identifier()->id);
        self::assertSame(5, self::read($pivots[1], 'weight'));
    }

    public function testARelationTheResponseLeftOutHasNoPivots(): void
    {
        self::assertSame([], self::pivots(null));
    }

    public function testAFieldTheServerAlwaysRendersIsMalformedWhenMissing(): void
    {
        $pivots = self::pivots([['type' => 'tracks', 'id' => '4', 'meta' => ['pivot' => ['weight' => 1]]]]);

        $this->expectException(MalformedDocument::class);
        $this->expectExceptionMessage('The pivot of the "tracks" "4" linkage member has a "position" member that is not an integer.');

        self::read($pivots[0], 'position');
    }

    /**
     * A playlist's `orderedTracksPivots()`, from a response carrying `$linkage`.
     *
     * @param list<array<string, mixed>>|null $linkage the `orderedTracks` linkage, or null to leave the relation out
     *
     * @return list<Pivot>
     */
    private static function pivots(?array $linkage): array
    {
        $raw = ['type' => 'playlists', 'id' => '1', 'attributes' => ['title' => 'Late'], 'relationships' => []];
        if ($linkage !== null) {
            $raw['relationships']['orderedTracks'] = ['data' => $linkage];
        }

        $hydrate = [Generated::NAMESPACE . '\\Playlist', '_hydrate'];
        self::assertIsCallable($hydrate);
        $playlist = $hydrate($raw, ResourceContext::of(['data' => $raw]));
        self::assertIsObject($playlist);

        $read = [$playlist, 'orderedTracksPivots'];
        self::assertIsCallable($read);
        $pivots = $read();
        self::assertIsList($pivots);
        self::assertContainsOnlyInstancesOf(Pivot::class, $pivots);

        return $pivots;
    }

    /** A generated pivot accessor, called by name since analysis cannot see the golden output. */
    private static function read(Pivot $pivot, string $field): mixed
    {
        $read = [$pivot, $field];
        self::assertIsCallable($read);

        return $read();
    }
}
