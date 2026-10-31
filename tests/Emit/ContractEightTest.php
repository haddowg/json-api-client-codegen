<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * What generator contract 8 changes in the generated client: a pivot's read and write schemas
 * are separate, a relation's paths use its URI field name, and a write-only relation is marked
 * `writeOnly` in the write bodies.
 *
 * The unmodified document's output is pinned by the golden files; the cases that change the
 * document change one thing in it.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(ReadModelEmitter::class)]
final class ContractEightTest extends TestCase
{
    public function testAPivotIsReadFromItsResponseSchema(): void
    {
        $playlists = Generated::file('resource-playlists.php');

        self::assertStringContainsString('final class PlaylistOrderedTracksPivot extends Pivot', $playlists);
        self::assertStringContainsString("    public function addedAt(): \\DateTimeImmutable\n    {\n        return \$this->_dateTime('addedAt');", $playlists, 'read-only, and always rendered');
        self::assertStringContainsString("    public function weight(): ?int\n    {\n        return \$this->_nullableInt('weight');", $playlists, 'always rendered, and may be null');
        self::assertStringContainsString("/** @var positive-int \$value */\n        \$value = \$this->_int('position');", $playlists, 'narrowed by its minimum');
        self::assertStringContainsString("    public function orderedTracksPivots(): array\n", $playlists);
    }

    public function testAPivotFieldTheServerMayLeaveOutReadsAsNullable(): void
    {
        $playlists = self::generate(static function (array $document): array {
            $document['components']['schemas']['PlaylistsOrderedTracksPivot']['required'] = ['position'];

            return $document;
        })['resource-playlists.php'];

        self::assertStringContainsString("    public function addedAt(): ?\\DateTimeImmutable\n    {\n        return \$this->_nullableDateTime('addedAt');", $playlists);
        self::assertStringContainsString("@return positive-int\n", $playlists, 'a rendered field keeps its strict reader');
    }

    public function testTheEdgeIsBuiltFromTheWriteSchema(): void
    {
        $playlists = self::generate(static function (array $document): array {
            $document['components']['schemas']['PlaylistsOrderedTracksWritePivot']['properties']['note'] = ['type' => 'string'];

            return $document;
        })['resource-playlists.php'];

        $edge = self::declaration($playlists, 'final class PlaylistOrderedTracksEdge');
        self::assertStringContainsString('public readonly string|Missing $note = Missing::Value,', $edge, 'a field only a write sends is on the edge');
        self::assertStringNotContainsString('$addedAt', $edge, 'a field no write sends is not');
        self::assertStringNotContainsString('function note()', self::declaration($playlists, 'final class PlaylistOrderedTracksPivot'), 'and the read has no accessor for it');
    }

    public function testAnOlderDocumentsSharedPivotStillDropsItsReadOnlyFieldsFromTheEdge(): void
    {
        $playlists = self::generate(static function (array $document): array {
            // Before contract 8 one pivot schema served both directions, read-only fields flagged.
            $document['info']['x-generator']['contract'] = 7;
            $schemas = &$document['components']['schemas'];
            $schemas['PlaylistsOrderedTracksPivot']['properties']['addedAt']['readOnly'] = true;
            $schemas['PlaylistsOrderedTracksPivot']['required'] = ['position'];
            foreach (['PlaylistsOrderedTracksWriteRelationship', 'PlaylistsOrderedTracksRelationshipRequest'] as $name) {
                $schemas[$name]['properties']['data']['items']['allOf'][1]['properties']['meta']['properties']['pivot'] = ['$ref' => '#/components/schemas/PlaylistsOrderedTracksPivot'];
            }

            return $document;
        })['resource-playlists.php'];

        $edge = self::declaration($playlists, 'final class PlaylistOrderedTracksEdge');
        self::assertStringContainsString('public readonly int $position,', $edge);
        self::assertStringNotContainsString('$addedAt', $edge);
        self::assertStringContainsString('(`addedAt` is read-only)', $playlists);
        self::assertStringContainsString('public function addedAt(): ?\DateTimeImmutable', $playlists, 'an older read lists no rendered fields as required');
    }

    public function testARenamedRelationsEndpointsAreFoundByOperationId(): void
    {
        $api = self::descriptor(static function (array $document): array {
            // `withUriFieldName('ordered-tracks')`: the paths move, the operation ids keep the name.
            foreach (['/playlists/{id}/orderedTracks' => '/playlists/{id}/ordered-tracks', '/playlists/{id}/relationships/orderedTracks' => '/playlists/{id}/relationships/ordered-tracks'] as $from => $to) {
                $document['paths'][$to] = $document['paths'][$from];
                unset($document['paths'][$from]);
            }

            return $document;
        });

        $ordered = $api->resource('playlists')?->relation('orderedTracks');
        self::assertNotNull($ordered);
        self::assertSame('/playlists/{id}/ordered-tracks', $ordered->relatedRead?->path);
        self::assertSame('/playlists/{id}/relationships/ordered-tracks', $ordered->relationshipRead?->path);
        self::assertSame(['add', 'remove', 'replace'], \array_keys($ordered->mutations));

        $relation = self::declaration(
            Generator::default()->generate($api, Generated::config(), '')['resource-playlists.php'],
            'final class PlaylistOrderedTracksRelation',
        );
        self::assertStringContainsString("'/playlists/' . \$this->id . '/ordered-tracks'", $relation);
        self::assertStringContainsString("'/playlists/' . \$this->id . '/relationships/ordered-tracks'", $relation);
        self::assertStringNotContainsString("/orderedTracks'", $relation);
    }

    public function testAPathNamedForARelationButOwnedByAnotherIsNotTaken(): void
    {
        $api = self::descriptor(static function (array $document): array {
            // `orderedTracks` serves its related read under `ordered-tracks`, and `tracks` under
            // `orderedTracks`: the segment named for one relation belongs to the other.
            $document['paths']['/playlists/{id}/ordered-tracks'] = $document['paths']['/playlists/{id}/orderedTracks'];
            $document['paths']['/playlists/{id}/orderedTracks'] = $document['paths']['/playlists/{id}/tracks'];
            unset($document['paths']['/playlists/{id}/tracks']);

            return $document;
        });

        $playlists = $api->resource('playlists');
        self::assertNotNull($playlists);
        self::assertSame('/playlists/{id}/ordered-tracks', $playlists->relation('orderedTracks')?->relatedRead?->path);
        self::assertSame('/playlists/{id}/orderedTracks', $playlists->relation('tracks')?->relatedRead?->path);
    }

    public function testADocumentWithoutOperationIdsIsReadByRelationName(): void
    {
        $api = self::descriptor(static function (array $document): array {
            foreach ($document['paths'] as $path => $item) {
                foreach ($item as $method => $operation) {
                    unset($document['paths'][$path][$method]['operationId']);
                }
            }

            return $document;
        });

        $ordered = $api->resource('playlists')?->relation('orderedTracks');
        self::assertNotNull($ordered);
        self::assertSame('/playlists/{id}/orderedTracks', $ordered->relatedRead?->path);
        self::assertSame('/playlists/{id}/relationships/orderedTracks', $ordered->relationshipRead?->path);
    }

    public function testARelationMarkedWriteOnlyIsWrittenAndNeverRead(): void
    {
        $api = self::descriptor(static function (array $document): array {
            // The marker alone decides it, even with the relation still in the read schema.
            foreach (['AlbumsCreateRequest', 'AlbumsUpdateRequest'] as $body) {
                $document['components']['schemas'][$body]['properties']['data']['properties']['relationships']['properties']['artist']['writeOnly'] = true;
            }

            return $document;
        });
        $albums = $api->resource('albums');
        self::assertNotNull($albums);

        self::assertArrayNotHasKey('artist', $albums->relations);
        self::assertArrayHasKey('artist', $albums->writeOnlyRelations);
        self::assertSame(['artists'], $albums->writeOnlyRelations['artist']->types);
        self::assertNotContains('artist', $albums->includable);

        $source = Generator::default()->generate($api, Generated::config(), '')['resource-albums.php'];
        self::assertStringNotContainsString('public function artist()', $source, 'no read model accessor');
        self::assertStringNotContainsString('function artistRef()', $source, 'and no linkage read');
        self::assertStringNotContainsString('interface AlbumHasArtist', $source, 'nothing to include');
        self::assertStringContainsString('public readonly Artist|Created|Identifier|Missing|null $artist = Missing::Value,', $source, 'the create DTO keeps it');
    }

    /** The declaration that starts at `$start`, up to the next top-level closing brace. */
    private static function declaration(string $source, string $start): string
    {
        $from = \strpos($source, $start);
        self::assertNotFalse($from, $start . ' is not in the generated source');
        $to = \strpos($source, "\n}\n", $from);
        self::assertNotFalse($to);

        return \substr($source, $from, $to - $from);
    }

    /**
     * @param \Closure(array<array-key, mixed>): array<array-key, mixed> $change
     *
     * @return array<string, string>
     */
    private static function generate(\Closure $change): array
    {
        return Generator::default()->generate(self::descriptor($change), Generated::config(), '');
    }

    /**
     * @param \Closure(array<array-key, mixed>): array<array-key, mixed> $change
     */
    private static function descriptor(\Closure $change): ApiDescriptor
    {
        $json = \file_get_contents(Fixtures::path('music-catalog.openapi.json'));
        self::assertIsString($json);
        $document = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        return DescriptorBuilder::build(SpecDocument::fromDecoded($change($document)));
    }
}
