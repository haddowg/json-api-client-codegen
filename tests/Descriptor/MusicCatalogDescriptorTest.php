<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Descriptor;

use haddowg\JsonApiCodegen\Descriptor\ActionInput;
use haddowg\JsonApiCodegen\Descriptor\ActionOutput;
use haddowg\JsonApiCodegen\Descriptor\ActionScope;
use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\RelationVerb;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\GoldenFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(ApiDescriptor::class)]
#[CoversClass(ResourceDescriptor::class)]
final class MusicCatalogDescriptorTest extends TestCase
{
    use GoldenFile;

    public function testTheWholeDescriptorMatchesTheGoldenFile(): void
    {
        $this->assertMatchesGolden(
            'music-catalog.descriptor.json',
            DescriptorSerializer::toJson(Fixtures::musicCatalogDescriptor()),
        );
    }

    public function testSerialisationIsStableAcrossBuilds(): void
    {
        $document = Fixtures::musicCatalog();

        self::assertSame(
            DescriptorSerializer::toJson(DescriptorBuilder::build($document)),
            DescriptorSerializer::toJson(DescriptorBuilder::build($document)),
        );
    }

    public function testEveryResourceSchemaBecomesAType(): void
    {
        self::assertSame([
            'albums', 'artists', 'charts', 'countries', 'devices', 'favorites', 'genres',
            'libraries', 'playlists', 'products', 'public-profiles', 'tracks', 'users',
        ], \array_keys(Fixtures::musicCatalogDescriptor()->resources));
    }

    public function testAttributeFormatsCarryTheEnumComponentTheyAreDrawnFrom(): void
    {
        $albums = $this->resource('albums');

        self::assertSame('date-time', $albums->attributes['releasedAt']->format);
        self::assertNull($albums->attributes['releasedAt']->enum);
        self::assertSame('number', $albums->attributes['averageRating']->format);
        self::assertSame('AlbumStatus', $albums->attributes['status']->enum);
    }

    public function testEnumsCarryTheirVarnamesAndDescriptions(): void
    {
        $enum = Fixtures::musicCatalogDescriptor()->enum('AlbumStatus');

        self::assertNotNull($enum);
        self::assertSame(['upcoming', 'released', 'withdrawn'], \array_map(static fn($case) => $case->value, $enum->cases));
        self::assertSame(['Upcoming', 'Released', 'Withdrawn'], \array_map(static fn($case) => $case->name, $enum->cases));
        self::assertSame('Announced but not yet on sale.', $enum->cases[0]->description);
    }

    public function testAToOnePermitsOnlySet(): void
    {
        $artist = $this->resource('albums')->relation('artist');

        self::assertNotNull($artist);
        self::assertSame(Cardinality::One, $artist->cardinality);
        self::assertSame([RelationVerb::Set], $artist->mutations);
        self::assertFalse($artist->permits(RelationVerb::Add));
    }

    public function testMutationVerbsFollowTheMethodsTheEndpointAdvertises(): void
    {
        $playlists = $this->resource('tracks')->relation('playlists');

        // The relationship endpoint advertises POST and DELETE but no PATCH.
        self::assertNotNull($playlists);
        self::assertSame([RelationVerb::Add, RelationVerb::Remove], $playlists->mutations);
        self::assertFalse($playlists->permits(RelationVerb::Replace));
    }

    public function testAPolymorphicRelationCarriesEveryRelatedType(): void
    {
        $favoritable = $this->resource('favorites')->relation('favoritable');

        self::assertNotNull($favoritable);
        self::assertSame(Cardinality::One, $favoritable->cardinality);
        self::assertSame(['tracks', 'albums', 'artists'], $favoritable->types);
    }

    public function testPivotFieldsCarryWritabilityFromTheSpec(): void
    {
        $ordered = $this->resource('playlists')->relation('orderedTracks');

        self::assertNotNull($ordered);
        self::assertTrue($ordered->pivot);
        self::assertSame(['addedAt', 'position', 'weight'], \array_keys($ordered->pivotFields));

        self::assertTrue($ordered->pivotFields['addedAt']->readOnly);
        self::assertFalse($ordered->pivotFields['addedAt']->required);

        self::assertFalse($ordered->pivotFields['position']->readOnly);
        self::assertTrue($ordered->pivotFields['position']->required);
        self::assertSame('integer', $ordered->pivotFields['position']->format);

        self::assertFalse($ordered->pivotFields['weight']->readOnly);
        self::assertFalse($ordered->pivotFields['weight']->required);
    }

    public function testRelationsWithNoEndpointsHaveBothSuppressionFlagsOff(): void
    {
        $albums = $this->resource('albums')->relation('tracks');
        self::assertNotNull($albums);
        self::assertTrue($albums->related);
        self::assertTrue($albums->relationship);

        // `devices` declares an empty relationships object, so nothing survives to be suppressed.
        self::assertSame([], $this->resource('devices')->relations);
    }

    public function testPaginatorIsReadPerTypeAndNotAssumed(): void
    {
        self::assertSame(PaginatorKind::Page, $this->resource('albums')->paginator);
        self::assertSame(PaginatorKind::None, $this->resource('charts')->paginator);
        self::assertSame(PaginatorKind::None, $this->resource('countries')->paginator);
    }

    public function testNoRelationDivergesFromItsRelatedTypesPaginator(): void
    {
        foreach (Fixtures::musicCatalogDescriptor()->resources as $resource) {
            foreach ($resource->relations as $relation) {
                self::assertNull($relation->paginator, $resource->type . '.' . $relation->name);
            }
        }
    }

    public function testClientIdPolicyFollowsTheCreateDocument(): void
    {
        self::assertSame(ClientIdPolicy::Forbidden, $this->resource('albums')->clientId);
        self::assertSame(ClientIdPolicy::Required, $this->resource('genres')->clientId);
        self::assertSame(ClientIdPolicy::Forbidden, $this->resource('charts')->clientId);
    }

    public function testOnlyTheOperationsTheDocumentAdvertisesArePresent(): void
    {
        self::assertSame(
            ['create', 'delete', 'fetchMany', 'fetchOne', 'fetchRelated', 'fetchRelationship', 'update'],
            \array_keys($this->resource('albums')->operations),
        );

        $charts = $this->resource('charts');
        self::assertSame(['fetchMany', 'fetchOne'], \array_keys($charts->operations));
        self::assertNull($charts->operation(OperationKind::Create));
        self::assertNull($charts->operation(OperationKind::Delete));
    }

    public function testOperationsCarryTheirOwnErrorStatuses(): void
    {
        $create = $this->resource('albums')->operation(OperationKind::Create);
        $delete = $this->resource('albums')->operation(OperationKind::Delete);

        self::assertNotNull($create);
        self::assertNotNull($delete);
        self::assertSame([400, 401, 403, 404, 406, 409, 415, 422, 500], $create->errorStatuses);
        self::assertSame([400, 401, 403, 404, 406, 500], $delete->errorStatuses);
    }

    public function testCountableCarriesItsTokensAndNegotiationProfile(): void
    {
        $countable = $this->resource('albums')->countable;

        self::assertNotNull($countable);
        self::assertSame(['tracks'], $countable->tokens);
        self::assertSame('https://haddowg.github.io/json-api/profiles/countable/', $countable->profile);
        self::assertNull($this->resource('charts')->countable);
    }

    public function testIncludeAndSortTokensComeFromTheCollectionRead(): void
    {
        $albums = $this->resource('albums');

        self::assertSame(['artist', 'artist.albums', 'tracks', 'tracks.album', 'tracks.playlists'], $albums->includable);
        self::assertSame(['title', '-title', 'releasedAt', '-releasedAt', 'status', '-status'], $albums->sortable);
    }

    public function testAFilterWithAnEmptySchemaIsModelledAsUnknown(): void
    {
        $filters = $this->resource('albums')->filterable;

        self::assertSame(['artist.name', 'q', 'rating', 'releasedAt', 'title', 'tracks'], \array_keys($filters));
        self::assertNull($filters['title']->schema, 'an empty schema means the value type is unknown, not string');
        self::assertNull($filters['artist.name']->schema);

        $rating = $filters['rating']->schema;
        self::assertNotNull($rating);
        self::assertSame(['object'], $rating->types);
        self::assertSame(['max', 'min'], \array_keys($rating->properties));
        self::assertSame(['number'], $rating->properties['min']->types);
        self::assertSame('date-time', $filters['releasedAt']->schema?->properties['min']->format);
    }

    public function testActionsCarryTheirInputAndOutputShapes(): void
    {
        $actions = $this->resource('albums')->actions;
        self::assertSame(['artwork', 'reissue', 'summary'], \array_keys($actions));

        self::assertSame(ActionScope::Collection, $actions['summary']->scope);
        self::assertSame(ActionInput::None, $actions['summary']->input);
        self::assertSame(ActionOutput::Meta, $actions['summary']->output);

        self::assertSame(ActionScope::Resource, $actions['artwork']->scope);
        self::assertSame(ActionInput::Raw, $actions['artwork']->input);
        self::assertSame('application/octet-stream', $actions['artwork']->contentType);
        self::assertSame(ActionOutput::None, $actions['artwork']->output);

        self::assertSame(ActionInput::Document, $actions['reissue']->input);
        self::assertSame('albums', $actions['reissue']->inputType);
        self::assertSame('albums', $actions['reissue']->outputType);
        self::assertSame(Cardinality::One, $actions['reissue']->outputCardinality);
        self::assertSame('POST', $actions['reissue']->method);
    }

    public function testTheAtomicCapabilityIsFoundByItsExtMediaType(): void
    {
        $atomic = Fixtures::musicCatalogDescriptor()->atomic;

        self::assertNotNull($atomic);
        self::assertSame('/operations', $atomic->path);
        self::assertStringContainsString('ext="https://jsonapi.org/ext/atomic"', $atomic->mediaType);
    }

    public function testATypeWithNoEndpointsCarriesNoOperations(): void
    {
        $users = $this->resource('users');

        self::assertSame([], $users->operations);
        self::assertSame([], $users->attributes);
        self::assertSame(PaginatorKind::None, $users->paginator);
    }

    private function resource(string $type): ResourceDescriptor
    {
        $resource = Fixtures::musicCatalogDescriptor()->resource($type);
        self::assertNotNull($resource, 'the fixture declares a ' . $type . ' type');

        return $resource;
    }
}
