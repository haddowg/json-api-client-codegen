<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Naming::class)]
final class NamingTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function types(): iterable
    {
        yield 'albums' => ['albums', 'Album'];
        yield 'countries' => ['countries', 'Country'];
        yield 'libraries' => ['libraries', 'Library'];
        yield 'releases' => ['releases', 'Release'];
        yield 'catalog-exports' => ['catalog-exports', 'CatalogExport'];
        yield 'public-profiles' => ['public-profiles', 'PublicProfile'];
        yield 'statuses' => ['statuses', 'Status'];
        yield 'addresses' => ['addresses', 'Address'];
        yield 'boxes' => ['boxes', 'Box'];
    }

    #[DataProvider('types')]
    public function testATypeNamesItsResourceClassFromItsSingular(string $type, string $class): void
    {
        self::assertSame($class, Naming::resource($type));
    }

    public function testATypeThatIsItsOwnSingularGivesItsAccessorASuffix(): void
    {
        self::assertSame('People', Naming::resource('people'));
        self::assertSame('PeopleAccessor', Naming::accessor('people'));
        self::assertSame('Healthcheck', Naming::resource('healthcheck'));
        self::assertSame('HealthcheckAccessor', Naming::accessor('healthcheck'));
        self::assertSame('Albums', Naming::accessor('albums'));
    }

    public function testEveryFixtureTypeGetsADistinctClassAndAccessor(): void
    {
        $classes = [];
        foreach (\array_keys(Fixtures::musicCatalogDescriptor()->resources) as $type) {
            $classes[] = \strtolower(Naming::resource($type));
            $classes[] = \strtolower(Naming::accessor($type));
        }

        self::assertSame(\count($classes), \count(\array_unique($classes)));
    }

    public function testTheArtefactsNamesAreReproduced(): void
    {
        self::assertSame('Albums', Naming::accessor('albums'));
        self::assertSame('AlbumBase', Naming::base('albums'));
        self::assertSame('AlbumHasArtist', Naming::marker('albums', 'artist'));
        self::assertSame('AlbumTracksRelation', Naming::relationHandle('albums', 'tracks'));
        self::assertSame('AlbumCollectionActions', Naming::perType('albums', 'CollectionActions'));
        self::assertSame('AlbumFilterShape', Naming::alias('albums', 'FilterShape'));
        self::assertSame('MusicCatalogClient', Naming::client('MusicCatalog'));
        self::assertSame('whereArtistName', Naming::filterMethod('artist.name'));
        self::assertSame('withTracks', Naming::includeMethod('tracks'));
        self::assertSame('releaseInfo', Naming::member('release-info'));
        self::assertSame('releaseInfo', Naming::member('releaseInfo'));
    }
}
