<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\Reads\FilterTypes;
use haddowg\JsonApiCodegen\Emit\Writes\WriteModel;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * What generator contract 5 changes in the generated client, against variations of the
 * music-catalog document: constraints read per schema, and write-only relations.
 *
 * The unmodified document's output is pinned by the golden files; these cases change one thing
 * in it, so each assertion is about that change and nothing else.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(WriteModel::class)]
#[CoversClass(FilterTypes::class)]
final class ContractFiveTest extends TestCase
{
    public function testEachContextIsNarrowedFromItsOwnSchema(): void
    {
        $tracks = self::generate(static function (array $document): array {
            $document['components']['schemas']['TracksUpdateAttributes']['properties']['trackNumber']['maximum'] = 99;

            return $document;
        })['resource-tracks.php'];

        self::assertStringContainsString("@return positive-int\n", $tracks, 'the read keeps the floor and no cap');
        self::assertStringContainsString('@param positive-int $trackNumber', $tracks, 'the create keeps the floor and no cap');
        self::assertStringContainsString('@param int<1, 99>|Missing $trackNumber', $tracks, 'the update carries its own cap');
    }

    public function testAnOlderDocumentNarrowsItsWritesAndNotItsReads(): void
    {
        $tracks = self::generate(static function (array $document): array {
            $document['info']['x-generator']['contract'] = 4;

            return $document;
        })['resource-tracks.php'];

        self::assertStringContainsString("    public function title(): string\n    {\n        return \$this->_string('title');", $tracks, 'a contract 4 read schema took the update context\'s keywords');
        self::assertStringContainsString('@param non-empty-string $title', $tracks, 'a write schema\'s held for its own write');
    }

    public function testAFilterValueIsNarrowedByItsSchema(): void
    {
        $tracks = self::generate(static function (array $document): array {
            foreach ($document['paths']['/tracks']['get']['parameters'] as $i => $parameter) {
                $keyword = match ($parameter['name'] ?? null) {
                    'filter[title]' => 'minLength',
                    'filter[genres]' => 'minItems',
                    default => null,
                };
                if ($keyword !== null) {
                    $document['paths']['/tracks']['get']['parameters'][$i]['schema'][$keyword] = 1;
                }
            }

            return $document;
        })['resource-tracks.php'];

        self::assertStringContainsString('@phpstan-type TrackFilterShape array{title?: non-empty-string, explicit?: bool, genres?: non-empty-list<string>, q?: string}', $tracks);
    }

    public function testAWriteOnlyRelationIsWrittenAndNeverRead(): void
    {
        $api = self::descriptor(self::withWriteOnlyArtist(...));
        $albums = $api->resource('albums');
        self::assertNotNull($albums);

        self::assertArrayNotHasKey('artist', $albums->relations);
        self::assertArrayHasKey('artist', $albums->writeOnlyRelations);
        self::assertSame(['artists'], $albums->writeOnlyRelations['artist']->types);
        self::assertNull($albums->writeOnlyRelations['artist']->relationshipRead);

        $source = Generator::default()->generate($api, Generated::config(), '')['resource-albums.php'];

        self::assertStringNotContainsString('public function artist()', $source, 'no read model accessor');
        self::assertStringNotContainsString('interface AlbumHasArtist', $source, 'nothing to include');
        self::assertStringContainsString('public readonly Artist|Created|Identifier|Missing|null $artist = Missing::Value,', $source, 'the create DTO keeps it');
        self::assertStringContainsString('public function artist(Artist|Created|Identifier|null $artist): self', $source, 'and both builders set it');
        self::assertStringContainsString("'artist'", $source);
    }

    public function testAMalformedConstraintNamesItsPointer(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('components.schemas.TracksAttributes.properties.trackNumber.minimum');

        self::descriptor(static function (array $document): array {
            $document['components']['schemas']['TracksAttributes']['properties']['trackNumber']['minimum'] = 'one';

            return $document;
        });
    }

    /**
     * What core 1.5 projects for an unconditional `writeOnly()` relation: gone from the read
     * schema, its reads and every `include` and `fields` list, still in both write documents.
     *
     * @param array<array-key, mixed> $document
     *
     * @return array<array-key, mixed>
     */
    private static function withWriteOnlyArtist(array $document): array
    {
        $document = self::without($document, 'components', 'schemas', 'AlbumsResource', 'properties', 'relationships', 'properties', 'artist');
        $document = self::without($document, 'paths', '/albums/{id}/artist');
        $document = self::without($document, 'paths', '/albums/{id}/relationships/artist', 'get');

        return self::withoutArtistTokens($document);
    }

    /**
     * The document with the member at a path removed.
     *
     * @param array<array-key, mixed> $node
     *
     * @return array<array-key, mixed>
     */
    private static function without(array $node, string $key, string ...$path): array
    {
        if ($path === []) {
            unset($node[$key]);

            return $node;
        }

        $child = $node[$key] ?? null;
        self::assertIsArray($child);
        $node[$key] = self::without($child, ...$path);

        return $node;
    }

    /**
     * @param array<array-key, mixed> $node
     *
     * @return array<array-key, mixed>
     */
    private static function withoutArtistTokens(array $node): array
    {
        if (isset($node['name'], $node['schema']) && \is_string($node['name']) && \is_array($node['schema'])
            && ($node['name'] === 'include' || $node['name'] === 'fields[albums]')) {
            $schema = $node['schema'];
            $items = $schema['items'] ?? null;
            if (\is_array($items) && \is_array($items['enum'] ?? null)) {
                $items['enum'] = \array_values(\array_filter(
                    $items['enum'],
                    static fn(mixed $token): bool => !\is_string($token) || !\in_array('artist', \explode('.', $token), true),
                ));
                $schema['items'] = $items;
                $node['schema'] = $schema;
            }

            return $node;
        }

        foreach ($node as $key => $child) {
            if (\is_array($child)) {
                $node[$key] = self::withoutArtistTokens($child);
            }
        }

        return $node;
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
