<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 9 writes a `$ref` that carries an `example`, `examples` or `default` as a
 * single-entry `allOf` holding only the `$ref`, with those keywords beside it (core ADR 0165).
 *
 * The music-catalog document already wraps every link member that way. These tests also wrap the
 * `albums` status attribute, an enum, on every schema that carries it, and the related tracks
 * response, then check the client is the one generated from the same document with every wrapper
 * written as a bare `$ref` instead.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(SpecDocument::class)]
final class ContractNineTest extends TestCase
{
    private const string STATUS = '#/components/schemas/AlbumStatus';

    public function testAWrappedRefGeneratesWhatABareRefDoes(): void
    {
        $wrapped = self::wrapped();
        $bare = self::bare($wrapped);
        self::assertIsArray($bare);

        self::assertNotSame($wrapped, $bare, 'the bare form differs from the wrapped one');
        self::assertSame(
            DescriptorSerializer::toJson(self::descriptor($bare)),
            DescriptorSerializer::toJson(self::descriptor($wrapped)),
        );
        self::assertSame(self::generate($bare), self::generate($wrapped));
    }

    public function testAWrappedEnumAttributeKeepsItsEnumType(): void
    {
        $albums = self::descriptor(self::wrapped())->resource('albums');
        self::assertNotNull($albums);

        self::assertSame('AlbumStatus', $albums->attributes['status']->enum);
        self::assertTrue($albums->attributes['status']->nullable, 'the nullable union around the wrapper is still read');
        self::assertNull($albums->attributes['status']->schema, 'an enum is not a composed schema');
        self::assertSame('AlbumStatus', $albums->createAttributes['status']->enum);
        self::assertSame('AlbumStatus', $albums->updateAttributes['status']->enum);

        $source = self::generate(self::wrapped())['resource-albums.php'];
        self::assertStringContainsString('public function status(): ?AlbumStatus', $source);
        self::assertStringContainsString('public readonly AlbumStatus|Missing $status = Missing::Value,', $source);
    }

    public function testTheWrappersDefaultIsKept(): void
    {
        $albums = self::descriptor(self::wrapped())->resource('albums');
        self::assertNotNull($albums);

        self::assertSame('upcoming', $albums->createAttributes['status']->constraints->default);
        self::assertSame('released', $albums->attributes['status']->constraints->default, 'from the branch of the nullable union');
    }

    public function testAWrappedRelatedResponseIsStillARelatedCollection(): void
    {
        $tracks = self::descriptor(self::wrapped())->resource('albums')?->relation('tracks');
        $unchanged = Fixtures::musicCatalogDescriptor()->resource('albums')?->relation('tracks');
        self::assertNotNull($tracks);
        self::assertNotNull($unchanged);

        self::assertSame('/albums/{id}/tracks', $tracks->relatedRead?->path);
        self::assertEquals($unchanged->paginator, $tracks->paginator);
    }

    /**
     * The fixture, with the status attribute and the related tracks response wrapped.
     *
     * @return array<array-key, mixed>
     */
    private static function wrapped(): array
    {
        $json = \file_get_contents(Fixtures::path('music-catalog.openapi.json'));
        self::assertIsString($json);
        $document = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        $wrap = static function (array $document): array {
            $description = 'Where the album sits in its release lifecycle.';
            $document['components']['schemas']['AlbumsAttributes']['properties']['status'] = [
                'anyOf' => [
                    ['description' => $description, 'allOf' => [['$ref' => self::STATUS]], 'default' => 'released', 'example' => 'released'],
                    ['type' => 'null'],
                ],
            ];
            foreach (['AlbumsCreateAttributes', 'AlbumsUpdateAttributes'] as $name) {
                $document['components']['schemas'][$name]['properties']['status'] = ['description' => $description, 'allOf' => [['$ref' => self::STATUS]], 'default' => 'upcoming', 'example' => 'upcoming'];
            }
            $document['paths']['/albums/{id}/tracks']['get']['responses']['200']['content']['application/vnd.api+json']['schema'] = [
                'allOf' => [['$ref' => '#/components/schemas/AlbumsTracksRelatedCollection']],
                'example' => ['data' => []],
            ];

            return $document;
        };

        return $wrap($document);
    }

    /**
     * The same document with every single-entry `allOf` that holds only a `$ref` written as the
     * bare `$ref`, its sibling keywords kept beside it, which is how contract 8 wrote them.
     */
    private static function bare(mixed $node): mixed
    {
        if (!\is_array($node)) {
            return $node;
        }

        $node = \array_map(self::bare(...), $node);
        $allOf = $node['allOf'] ?? null;
        if (\is_array($allOf) && \count($allOf) === 1 && \is_array($allOf[0] ?? null) && \array_keys($allOf[0]) === ['$ref']) {
            unset($node['allOf']);
            $node = ['$ref' => $allOf[0]['$ref'], ...$node];
        }

        return $node;
    }

    /** @param array<array-key, mixed> $document */
    private static function descriptor(array $document): ApiDescriptor
    {
        return DescriptorBuilder::build(SpecDocument::fromDecoded($document));
    }

    /**
     * @param array<array-key, mixed> $document
     *
     * @return array<string, string>
     */
    private static function generate(array $document): array
    {
        return Generator::default()->generate(self::descriptor($document), Generated::config(), '');
    }
}
