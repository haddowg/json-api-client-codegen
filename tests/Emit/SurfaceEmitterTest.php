<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Emit\Surface\RelationSource;
use haddowg\JsonApiCodegen\Emit\Surface\SurfaceEmitter;
use haddowg\JsonApiCodegen\Emit\Surface\Throws;
use haddowg\JsonApiCodegen\Emit\Surface\TypeSurface;
use haddowg\JsonApiCodegen\Tests\Support\ArtefactParity;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SurfaceEmitter::class)]
#[CoversClass(RelationSource::class)]
#[CoversClass(ActionSource::class)]
#[CoversClass(TypeSurface::class)]
#[CoversClass(Throws::class)]
final class SurfaceEmitterTest extends TestCase
{
    /**
     * Where the artefact's `@throws` lists disagree with the error statuses the document declares.
     *
     * The artefact was written by hand and several of its lists are short of, or beyond, what the
     * operation's `responses` say. The generated lists come from the document, so the difference
     * is removed from both sides here, each entry named, and everything else must still match.
     *
     * @var array<string, array{generated?: array<string, list<string>>, artefact?: array<string, list<string>>}>
     */
    private const array THROWS_DISCREPANCIES = [
        // DELETE /albums/{id} declares 400 and 406.
        'AlbumHandle' => ['generated' => ['delete' => ['BadRequest', 'NotAcceptable']]],
        // Both reads declare 406; PATCH declares 406, 409 and 415.
        'AlbumArtistRelation' => ['generated' => [
            'get' => ['NotAcceptable'],
            'related' => ['NotAcceptable'],
            'set' => ['NotAcceptable', 'Conflict', 'UnsupportedMediaType'],
        ]],
        // Both reads declare 406; POST, DELETE and PATCH each declare 406, 409 and 415.
        'AlbumTracksRelation' => ['generated' => [
            'get' => ['NotAcceptable'],
            'related' => ['NotAcceptable'],
            'add' => ['NotAcceptable', 'Conflict', 'UnsupportedMediaType'],
            'remove' => ['NotAcceptable', 'Conflict', 'UnsupportedMediaType'],
            'replace' => ['NotAcceptable', 'Conflict', 'UnsupportedMediaType'],
        ]],
        // POST /albums/{id}/-actions/artwork declares 406 and no 415.
        'AlbumActions' => [
            'generated' => ['artwork' => ['NotAcceptable']],
            'artefact' => ['artwork' => ['UnsupportedMediaType']],
        ],
        // POST /albums/-actions/summary declares 404, 415 and 422.
        'AlbumCollectionActions' => ['generated' => ['summary' => ['NotFound', 'UnsupportedMediaType', 'ValidationFailed']]],
    ];

    /**
     * The artefact orders `AlbumActions` as the document lists the paths; the descriptor, and so
     * the output, orders actions by name.
     */
    private const array ORDER_INSENSITIVE = ['AlbumActions'];

    /**
     * @return iterable<string, array{string}>
     */
    public static function artefactClasses(): iterable
    {
        foreach (['Albums', 'AlbumHandle', 'AlbumArtistRelation', 'AlbumTracksRelation', 'AlbumActions', 'AlbumCollectionActions'] as $class) {
            yield $class => [$class];
        }
    }

    #[DataProvider('artefactClasses')]
    public function testMatchesTheArtefact(string $class): void
    {
        $artefact = \file_get_contents(ArtefactParity::DIRECTORY . '/resource-albums.php');
        self::assertIsString($artefact);
        $generated = Generated::file('resource-albums.php');

        $known = self::THROWS_DISCREPANCIES[$class] ?? [];
        foreach ($known['generated'] ?? [] as $method => $exceptions) {
            $generated = self::withoutThrows($generated, $class, $method, $exceptions);
        }
        foreach ($known['artefact'] ?? [] as $method => $exceptions) {
            $artefact = self::withoutThrows($artefact, $class, $method, $exceptions);
        }
        if (\in_array($class, self::ORDER_INSENSITIVE, true)) {
            $artefact = self::membersByName($artefact, $class);
            $generated = self::membersByName($generated, $class);
        }

        self::assertSame(
            self::readable(ArtefactParity::normalised($artefact, $class)),
            self::readable(ArtefactParity::normalised($generated, $class)),
            \sprintf('%s differs from the artefact in code or type-bearing PHPDoc.', $class),
        );
    }

    public function testAnUndocumentedDiscrepancyIsStillCaught(): void
    {
        $generated = Generated::file('resource-albums.php');

        self::assertNotSame(
            ArtefactParity::normalised(self::withoutThrows($generated, 'AlbumHandle', 'delete', ['BadRequest']), 'AlbumHandle'),
            ArtefactParity::normalised($generated, 'AlbumHandle'),
            'the discrepancy filter removes what it names',
        );
        self::assertStringContainsString('@throws NotAcceptable', ArtefactParity::normalised(
            self::withoutThrows($generated, 'AlbumHandle', 'delete', ['BadRequest']),
            'AlbumHandle',
        ));
    }

    public function testEveryThrowsListFollowsTheOperationsDeclaredStatuses(): void
    {
        $source = Generated::file('resource-albums.php');

        self::assertSame(
            ['BadRequest', 'Unauthorized', 'Forbidden', 'NotFound', 'NotAcceptable', 'ServerError', 'TransportException'],
            self::throwsOf($source, 'AlbumHandle', 'delete'),
            'DELETE /albums/{id} declares 400, 401, 403, 404, 406 and 500',
        );
        self::assertContains('MalformedDocument', self::throwsOf($source, 'AlbumHandle', 'get'));
        self::assertNotContains('MalformedDocument', self::throwsOf($source, 'AlbumHandle', 'delete'));
    }

    public function testALinkageReadWithoutARelatedReadGetsNoRelatedMethod(): void
    {
        $source = self::unit('PlaylistOwnerRelation')->source;

        self::assertStringContainsString('public function get(): ?Identifier', $source);
        self::assertStringNotContainsString('function related(', $source, 'GET /playlists/{id}/owner is not advertised');
        self::assertStringContainsString('public function set(Identifier|null $target): void', $source, '`users` is not a type the document describes, so only a reference is accepted');
        self::assertStringNotContainsString('function add(', $source);
    }

    public function testAToManyGetsOnlyTheVerbsItsEndpointDeclares(): void
    {
        $source = self::unit('TrackPlaylistsRelation')->source;

        self::assertStringContainsString('public function add(array $playlists): void', $source);
        self::assertStringContainsString('public function remove(array $playlists): void', $source);
        self::assertStringNotContainsString('function replace(', $source, '/tracks/{id}/relationships/playlists declares no PATCH');
    }

    public function testAPivotRelationWritesThroughAnEdgeOfWritableFields(): void
    {
        $edge = self::unit('PlaylistOrderedTracksEdge')->source;

        self::assertStringContainsString('public readonly Identifier|Track $target,', $edge);
        self::assertStringContainsString('public readonly int $position,', $edge, 'position is required, so it takes no default');
        self::assertStringContainsString('public readonly int|Missing $weight = Missing::Value,', $edge);
        self::assertStringNotContainsString('addedAt', \str_replace('(`addedAt` is read-only)', '', $edge), 'addedAt is read-only');

        $handle = self::unit('PlaylistOrderedTracksRelation')->source;
        self::assertStringContainsString('@param list<PlaylistOrderedTracksEdge> $orderedTracks', $handle);
        self::assertStringContainsString('self::edges($orderedTracks)', $handle);
        self::assertMatchesRegularExpression('/@param list<Identifier\|Track> \$orderedTracks\s+\*\s+\*\s+@throws[^\n]+\n(?:[^\n]+\n)*?\s+public function remove/', $handle, 'a removal carries no pivot data');
    }

    public function testAPolymorphicRelationHydratesByType(): void
    {
        $source = self::unit('FavoriteFavoritableRelation')->source;

        self::assertStringContainsString('public function related(): Album|Artist|Track|null', $source);
        self::assertStringContainsString("'tracks' => Track::_hydrate(\$raw, \$context),", $source);
        self::assertStringContainsString('public function set(Album|Artist|Identifier|Track|null $target): void', $source);
    }

    public function testAWriteWhoseResponseIsNotTheResourceTakesNoProjection(): void
    {
        $source = self::unit('CatalogExports')->source;

        self::assertStringContainsString('array $input): Response', $source, 'POST /catalog-exports answers 202 with an export job');
        self::assertStringNotContainsString('$projection', $source);
        self::assertStringNotContainsString('MalformedDocument', $source);
    }

    public function testAReadWithoutProjectionTakesNone(): void
    {
        $source = self::unit('ChartHandle')->source;

        self::assertStringContainsString('public function get(): ChartBase', $source);
        self::assertStringContainsString('return Chart::_hydrate($raw, $context);', $source);
        self::assertStringNotContainsString('update(', $source);
        self::assertStringNotContainsString('create(', self::unit('Charts')->source);
    }

    public function testAClientGeneratedIdTravelsInTheCreateInputNotBesideIt(): void
    {
        $source = self::unit('Genres')->source;

        self::assertStringNotContainsString('        string $id,' . "\n", $source, 'the id is a member of GenreCreate, so create() takes no second one');
        self::assertStringContainsString('$document->toDocument()', $source);
        self::assertStringContainsString("carried as the input's `id`", $source);
    }

    public function testARelationNamedLikeAHandleVerbIsRoutedThroughAFallback(): void
    {
        $api = self::api([
            'get' => self::toOne('get'),
            'update' => self::toOne('update'),
        ]);

        $handle = self::unitsByName((new SurfaceEmitter())->emit($api, Generated::config()))['WidgetHandle']->source;

        self::assertStringContainsString('public function getRelation(): WidgetGetRelation', $handle);
        self::assertStringContainsString('public function updateRelation(): WidgetUpdateRelation', $handle);
    }

    public function testTwoRelationsGivingOneMethodNameAreRejected(): void
    {
        $api = self::api([
            'artist-name' => self::toOne('artist-name'),
            'artistName' => self::toOne('artistName'),
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('artistName()');
        \iterator_to_array((new SurfaceEmitter())->emit($api, Generated::config()), false);
    }

    public function testTheOutputIsTheSameOnEitherTarget(): void
    {
        $api = Fixtures::musicCatalogDescriptor();
        $for = static fn(PhpTarget $target): array => \array_map(
            static fn(ClassUnit $unit): string => $unit->source,
            self::unitsByName((new SurfaceEmitter())->emit($api, new GeneratorConfig(Generated::NAMESPACE, 'MusicCatalog', $target))),
        );

        self::assertSame($for(PhpTarget::Php84), $for(PhpTarget::Php83), 'the surface carries no property hooks');
    }

    private static function unit(string $name): ClassUnit
    {
        $units = self::unitsByName((new SurfaceEmitter())->emit(Fixtures::musicCatalogDescriptor(), Generated::config()));
        self::assertArrayHasKey($name, $units);

        return $units[$name];
    }

    /**
     * @param iterable<mixed> $emitted
     *
     * @return array<string, ClassUnit>
     */
    private static function unitsByName(iterable $emitted): array
    {
        $out = [];
        foreach ($emitted as $unit) {
            self::assertInstanceOf(ClassUnit::class, $unit);
            $out[$unit->name] = $unit;
        }

        return $out;
    }

    /**
     * @param array<string, RelationDescriptor> $relations
     */
    private static function api(array $relations): ApiDescriptor
    {
        $one = new OperationDescriptor('/widgets/{id}', 'GET', [404], null);

        return new ApiDescriptor([
            'widgets' => new ResourceDescriptor('widgets', [], [], [], $relations, ['fetchOne' => $one], PaginatorDescriptor::none(), ClientIdPolicy::Forbidden, null, [], [], [], []),
        ], [], null, 2, []);
    }

    private static function toOne(string $name): RelationDescriptor
    {
        $linkage = new OperationDescriptor('/widgets/{id}/relationships/' . $name, 'GET', [404], null);

        return new RelationDescriptor($name, Cardinality::One, ['widgets'], false, [], null, $linkage, [], null, null);
    }

    /**
     * The exception classes in one method's `@throws` list, in order.
     *
     * @return list<string>
     */
    private static function throwsOf(string $source, string $class, string $method): array
    {
        $docblock = self::docblockOf($source, $class, $method);
        \preg_match_all('/@throws (\w+)/', $docblock[2], $matches);

        return $matches[1];
    }

    /**
     * Drop the named `@throws` lines from one method's docblock.
     *
     * @param list<string> $exceptions
     */
    private static function withoutThrows(string $source, string $class, string $method, array $exceptions): string
    {
        [$start, $length, $docblock] = self::docblockOf($source, $class, $method);
        $filtered = (string) \preg_replace('/\n\s*\* @throws (?:' . \implode('|', $exceptions) . ')\b[^\n]*/', '', $docblock, -1, $count);
        self::assertSame(\count($exceptions), $count, \sprintf('Every named exception is in %s::%s()\'s @throws list.', $class, $method));

        return \substr_replace($source, $filtered, $start, $length);
    }

    /**
     * @return array{int, int, string} offset, length and text of the docblock above `$class::$method()`
     */
    private static function docblockOf(string $source, string $class, string $method): array
    {
        $classAt = \preg_match('/\n(?:final |abstract )?class ' . $class . '\b/', $source, $m, \PREG_OFFSET_CAPTURE) === 1 ? $m[0][1] : null;
        self::assertNotNull($classAt, 'No class ' . $class);
        $methodAt = \preg_match('/function ' . $method . '\(/', $source, $m, \PREG_OFFSET_CAPTURE, $classAt) === 1 ? $m[0][1] : null;
        self::assertNotNull($methodAt, 'No method ' . $class . '::' . $method . '()');
        $end = \strrpos(\substr($source, 0, $methodAt), '*/');
        $start = \strrpos(\substr($source, 0, $methodAt), '/**');
        self::assertIsInt($end);
        self::assertIsInt($start);

        return [$start, $end + 2 - $start, \substr($source, $start, $end + 2 - $start)];
    }

    /**
     * The source with one class's members reordered by method name, so a comparison ignores
     * member order.
     */
    private static function membersByName(string $source, string $class): string
    {
        $tokens = \PhpToken::tokenize($source);
        $count = \count($tokens);
        $open = null;
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->is(\T_CLASS) && ($tokens[$i + 2]->text ?? null) === $class) {
                for ($open = $i; $tokens[$open]->text !== '{'; $open++);
                break;
            }
        }
        self::assertNotNull($open, 'No class ' . $class);

        $members = [];
        $current = '';
        $name = '';
        $depth = 0;
        $close = $count;
        for ($i = $open + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            if ($depth === 0 && $token->text === '}') {
                $close = $i;
                break;
            }
            $current .= $token->text;
            if ($token->is(\T_FUNCTION) && $depth === 0) {
                $name = $tokens[$i + 2]->text ?? '';
            }
            if ($token->text === '{' || $token->is([\T_CURLY_OPEN, \T_DOLLAR_OPEN_CURLY_BRACES])) {
                $depth++;
            } elseif ($token->text === '}') {
                $depth--;
                if ($depth === 0) {
                    $members[] = [$name, \trim($current)];
                    $current = '';
                    $name = '';
                }
            }
        }
        \usort($members, static fn(array $a, array $b): int => $a[0] <=> $b[0]);

        $before = \implode('', \array_map(static fn(\PhpToken $token): string => $token->text, \array_slice($tokens, 0, $open + 1)));
        $after = \implode('', \array_map(static fn(\PhpToken $token): string => $token->text, \array_slice($tokens, $close)));

        return $before . "\n" . \implode("\n\n", \array_map(static fn(array $member): string => $member[1], $members)) . "\n" . $after;
    }

    private static function readable(string $normalised): string
    {
        return (string) \preg_replace('/ ([;{}]) /', " $1\n", $normalised);
    }
}
