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
            'albums', 'artists', 'catalog-exports', 'charts', 'countries', 'devices',
            'export-jobs', 'favorites', 'genres', 'libraries', 'playlists', 'products',
            'public-profiles', 'releases', 'tracks', 'users',
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

    public function testNullabilitySurvivesTheFormatHint(): void
    {
        $albums = $this->resource('albums');

        // Both are `number`; only one of them is `?float`.
        self::assertTrue($albums->attributes['averageRating']->nullable);
        self::assertSame('number', $albums->attributes['averageRating']->format);
        self::assertFalse($albums->attributes['explicit']->nullable);

        self::assertTrue($albums->attributes['availableFrom']->nullable);
        self::assertSame('date', $albums->attributes['availableFrom']->format);
        self::assertFalse($albums->attributes['releasedAt']->nullable);
        self::assertFalse($albums->attributes['status']->nullable);
    }

    public function testEveryNullableAttributeInTheDocumentIsMarked(): void
    {
        $nullable = [];
        foreach (Fixtures::musicCatalogDescriptor()->resources as $resource) {
            foreach ($resource->attributes as $attribute) {
                if ($attribute->nullable) {
                    $nullable[] = $resource->type . '.' . $attribute->name;
                }
            }
        }

        self::assertSame([
            'albums.artwork', 'albums.availableFrom', 'albums.availableUntil',
            'albums.averageRating', 'albums.releaseInfo',
            'artists.bio', 'artists.website',
            'playlists.externalId',
            'releases.availability', 'releases.dimensions', 'releases.format', 'releases.packaging',
            'tracks.previewOffset',
        ], $nullable);
    }

    public function testACompositeAttributeCarriesTheShapeItsFormatCannotExpress(): void
    {
        $releaseInfo = $this->resource('albums')->attributes['releaseInfo'];

        self::assertSame('object', $releaseInfo->format);
        self::assertTrue($releaseInfo->nullable);
        self::assertNotNull($releaseInfo->schema);
        self::assertSame(['catalogueNumber', 'label'], \array_keys($releaseInfo->schema->properties));
        self::assertSame(['string'], $releaseInfo->schema->properties['label']->types);

        $genres = $this->resource('tracks')->attributes['genres'];
        self::assertSame('array', $genres->format);
        self::assertSame(['string'], $genres->schema?->items?->types);
    }

    public function testAScalarAttributeCarriesNoSchemaBecauseTheFormatSaysItAll(): void
    {
        self::assertNull($this->resource('albums')->attributes['title']->schema);
        self::assertNull($this->resource('albums')->attributes['releasedAt']->schema);
    }

    public function testWriteAttributesAreReadFromTheirOwnDocuments(): void
    {
        $albums = $this->resource('albums');

        self::assertSame(
            ['availableFrom', 'availableUntil', 'explicit', 'releaseInfo', 'releasedAt', 'status', 'title'],
            \array_keys($albums->createAttributes),
        );
        self::assertSame(\array_keys($albums->createAttributes), \array_keys($albums->updateAttributes));
    }

    public function testRequiredIsCarriedPerMemberAndPerDocument(): void
    {
        $albums = $this->resource('albums');

        // Required on create, optional on update — the same member, two answers, which is why
        // the two sets are read separately.
        self::assertTrue($albums->createAttributes['title']->required);
        self::assertFalse($albums->updateAttributes['title']->required);
        self::assertFalse($albums->createAttributes['explicit']->required);
    }

    public function testACreateOnlyAttributeIsAbsentFromTheUpdateSet(): void
    {
        $artists = $this->resource('artists');

        self::assertArrayHasKey('createdAt', $artists->createAttributes);
        self::assertArrayNotHasKey('createdAt', $artists->updateAttributes);

        // catalog-exports accepts one attribute on create and none at all on update.
        $exports = $this->resource('catalog-exports');
        self::assertSame(['format'], \array_keys($exports->createAttributes));
        self::assertSame([], $exports->updateAttributes);
    }

    public function testAReadOnlyAttributeIsAbsentFromBothWriteSetsRatherThanMarked(): void
    {
        $albums = $this->resource('albums');

        self::assertArrayHasKey('artwork', $albums->attributes);
        self::assertArrayNotHasKey('artwork', $albums->createAttributes);
        self::assertArrayNotHasKey('artwork', $albums->updateAttributes);
        self::assertSame(['artwork', 'averageRating'], $albums->readOnlyAttributes());

        self::assertSame(['trackCount'], $this->resource('artists')->readOnlyAttributes());
        self::assertSame(['displayTitle'], $this->resource('tracks')->readOnlyAttributes());
    }

    public function testATypeWithNoWritesAcceptsNoAttributesAtAll(): void
    {
        $profiles = $this->resource('public-profiles');

        self::assertSame([], $profiles->createAttributes);
        self::assertSame([], $profiles->updateAttributes);
        self::assertSame(['displayName'], $profiles->readOnlyAttributes());
    }

    public function testWriteAttributesCarryTheSameValueMembersAsReadOnes(): void
    {
        $status = $this->resource('albums')->createAttributes['status'];

        self::assertSame('string', $status->format);
        self::assertSame('AlbumStatus', $status->enum);
        self::assertFalse($status->nullable);

        self::assertTrue($this->resource('albums')->createAttributes['availableFrom']->nullable);
        self::assertSame('date', $this->resource('albums')->createAttributes['availableFrom']->format);
    }

    public function testAComposedAttributeCarriesItsBranchesRatherThanReportingNoMembers(): void
    {
        // `availability` declares no members of its own — they live in its anyOf branches. Read
        // as a plain object it looks like an empty shape, which an emitter would believe.
        $availability = $this->resource('releases')->attributes['availability'];

        self::assertSame('object', $availability->format);
        self::assertNotNull($availability->schema);
        self::assertTrue($availability->schema->composed());
        self::assertSame([], $availability->schema->properties);
        self::assertCount(3, $availability->schema->anyOf);
        self::assertSame(['worldwide'], \array_keys($availability->schema->anyOf[0]->properties));
        self::assertSame(['regions'], \array_keys($availability->schema->anyOf[1]->properties));
    }

    public function testADiscriminatedUnionAttributeCarriesItsBranchesAndDiscriminator(): void
    {
        $format = $this->resource('releases')->attributes['format'];

        // A oneOf union declares no type, so the format hint has nothing to say. The branches
        // are the only description of the value there is.
        self::assertSame('unknown', $format->format);
        self::assertNotNull($format->schema);
        self::assertSame('medium', $format->schema->discriminator);
        self::assertCount(4, $format->schema->oneOf);
        self::assertCount(4, $format->schema->branches());
    }

    public function testNullabilityIsReadThroughAUnionBranch(): void
    {
        // The null arm is inside the oneOf, not in a type list, and it still makes the
        // attribute nullable.
        self::assertTrue($this->resource('releases')->attributes['format']->nullable);
        self::assertTrue($this->resource('releases')->attributes['availability']->nullable);
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
        self::assertSame([RelationVerb::Set], $artist->verbs());
        self::assertFalse($artist->permits(RelationVerb::Add));
    }

    public function testMutationVerbsFollowTheMethodsTheEndpointAdvertises(): void
    {
        $playlists = $this->resource('tracks')->relation('playlists');

        // The relationship endpoint advertises POST and DELETE but no PATCH.
        self::assertNotNull($playlists);
        self::assertSame([RelationVerb::Add, RelationVerb::Remove], $playlists->verbs());
        self::assertFalse($playlists->permits(RelationVerb::Replace));
        self::assertNull($playlists->mutation(RelationVerb::Replace));
    }

    public function testEachRelationEndpointCarriesItsOwnErrorStatuses(): void
    {
        $tracks = $this->resource('albums')->relation('tracks');
        self::assertNotNull($tracks);

        $read = $tracks->relationshipRead;
        $add = $tracks->mutation(RelationVerb::Add);
        self::assertNotNull($read);
        self::assertNotNull($add);

        // A linkage read cannot conflict or fail validation; a linkage mutation can. Taking the
        // union across the type would have put 409/415/422 on the read's @throws list.
        self::assertSame([400, 401, 403, 404, 406, 500], $read->errorStatuses);
        self::assertSame([400, 401, 403, 404, 406, 409, 415, 422, 500], $add->errorStatuses);
        self::assertSame('POST', $add->method);
        self::assertSame('/albums/{id}/relationships/tracks', $add->path);
    }

    public function testRelationEndpointsCarryTheirConcretePaths(): void
    {
        $tracks = $this->resource('albums')->relation('tracks');
        self::assertNotNull($tracks);
        self::assertNotNull($tracks->relatedRead);
        self::assertNotNull($tracks->relationshipRead);

        self::assertSame('/albums/{id}/tracks', $tracks->relatedRead->path);
        self::assertSame('GET', $tracks->relatedRead->method);
        self::assertSame('/albums/{id}/relationships/tracks', $tracks->relationshipRead->path);
    }

    public function testTheTypeLevelRelationTemplateUnionsWhatTheByNameDoorCouldHit(): void
    {
        $related = $this->resource('albums')->operation(OperationKind::FetchRelated);

        self::assertNotNull($related);
        self::assertSame('/albums/{id}/{rel}', $related->path);
        self::assertSame([400, 401, 403, 404, 406, 500], $related->errorStatuses);
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
        self::assertFalse($ordered->pivotFields['addedAt']->nullable);
        self::assertSame('date-time', $ordered->pivotFields['addedAt']->format);

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
        self::assertTrue($albums->related());
        self::assertTrue($albums->relationship());

        // `devices` declares an empty relationships object, so nothing survives to be suppressed.
        self::assertSame([], $this->resource('devices')->relations);
    }

    public function testPaginatorIsReadPerTypeAndNotAssumed(): void
    {
        $albums = $this->resource('albums')->paginator;

        self::assertSame(PaginatorKind::Page, $albums->kind);
        self::assertSame(['number', 'size'], $albums->parameters);
        self::assertTrue($albums->paginated());

        self::assertSame(PaginatorKind::None, $this->resource('charts')->paginator->kind);
        self::assertSame([], $this->resource('charts')->paginator->parameters);
        self::assertFalse($this->resource('countries')->paginator->paginated());
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
        self::assertSame(PaginatorKind::None, $users->paginator->kind);
    }

    private function resource(string $type): ResourceDescriptor
    {
        $resource = Fixtures::musicCatalogDescriptor()->resource($type);
        self::assertNotNull($resource, 'the fixture declares a ' . $type . ' type');

        return $resource;
    }
}
