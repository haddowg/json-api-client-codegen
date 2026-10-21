<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;

/**
 * The `artist` relation of an `albums` resource, hydrated.
 *
 * A marker carries no implementation. `withArtist()` intersects it into a query's projection, which
 * is what makes `artist()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.AlbumsResource.properties.relationships.artist`
 */
interface AlbumHasArtist
{
    /**
     * The related `artists` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `artist`
     */
    public function artist(): ?Artist;

    public ?Artist $artist { get; }
}

/**
 * The `tracks` relation of an `albums` resource, hydrated.
 *
 * A marker carries no implementation. `withTracks()` intersects it into a query's projection, which
 * is what makes `tracks()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.AlbumsResource.properties.relationships.tracks`
 */
interface AlbumHasTracks
{
    /**
     * @return ResourceCollection<Track>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `tracks`
     */
    public function tracks(): ResourceCollection;

    /** @var ResourceCollection<Track> */
    public ResourceCollection $tracks { get; }
}

/**
 * An `albums` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @phpstan-import-type AlbumReleaseInfoShape from Album
 *
 * @generated from `components.schemas.AlbumsResource`
 */
abstract class AlbumBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'albums';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $title { get => $this->title(); }

    public ?float $averageRating { get => $this->averageRating(); }

    public ?string $artwork { get => $this->artwork(); }

    public \DateTimeImmutable $releasedAt { get => $this->releasedAt(); }

    public bool $explicit { get => $this->explicit(); }

    public AlbumStatus $status { get => $this->status(); }

    public ?\DateTimeImmutable $availableFrom { get => $this->availableFrom(); }

    public ?\DateTimeImmutable $availableUntil { get => $this->availableUntil(); }

    /** @var AlbumReleaseInfoShape|null */
    public ?array $releaseInfo { get => $this->releaseInfo(); }

    public function id(): string
    {
        return $this->_identifier()->id;
    }

    /**
     * @return self::TYPE
     */
    public function type(): string
    {
        return self::TYPE;
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `title`
     */
    public function title(): string
    {
        return $this->_string('title');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `averageRating`
     */
    public function averageRating(): ?float
    {
        return $this->_nullableFloat('averageRating');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `artwork`
     */
    public function artwork(): ?string
    {
        return $this->_nullableString('artwork');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `releasedAt`
     */
    public function releasedAt(): \DateTimeImmutable
    {
        return $this->_dateTime('releasedAt');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `explicit`
     */
    public function explicit(): bool
    {
        return $this->_bool('explicit');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `status`
     */
    public function status(): AlbumStatus
    {
        return $this->_enum('status', AlbumStatus::class);
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `availableFrom`
     */
    public function availableFrom(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('availableFrom');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `availableUntil`
     */
    public function availableUntil(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('availableUntil');
    }

    /**
     * A nested object attribute, narrowed member by member: a member of the wrong type is dropped
     * rather than trusted.
     *
     * @return AlbumReleaseInfoShape|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `releaseInfo`
     */
    public function releaseInfo(): ?array
    {
        $raw = $this->_nullableMap('releaseInfo');

        if ($raw === null) {
            return null;
        }

        $shape = [];
        $label = $raw['label'] ?? null;
        $catalogueNumber = $raw['catalogueNumber'] ?? null;

        if (\is_string($label)) {
            $shape['label'] = $label;
        }

        if (\is_string($catalogueNumber)) {
            $shape['catalogueNumber'] = $catalogueNumber;
        }

        return $shape;
    }

    /**
     * The `artist` linkage, without hydrating anything. Never throws.
     */
    public function artistRef(): ?Identifier
    {
        return $this->_relationship('artist', true)->identifier();
    }

    /**
     * Whether the `artist` linkage points at anything. Never throws.
     */
    public function hasArtist(): bool
    {
        return !$this->_relationship('artist', true)->isEmpty();
    }

    /**
     * The `tracks` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function tracksRef(): array
    {
        return $this->_relationship('tracks', false)->identifiers();
    }

    /**
     * Whether the `tracks` linkage points at anything. Never throws.
     */
    public function hasTracks(): bool
    {
        return !$this->_relationship('tracks', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'artist'|'tracks' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, $name === 'artist');
    }
}

/**
 * An `albums` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `albums` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type AlbumReleaseInfoShape array{label?: string, catalogueNumber?: string}
 *
 * @generated from `components.schemas.AlbumsResource`
 */
final class Album extends AlbumBase implements AlbumHasArtist, AlbumHasTracks
{
    public ?Artist $artist { get => $this->artist(); }

    /** @var ResourceCollection<Track> */
    public ResourceCollection $tracks { get => $this->tracks(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `albums` one
     */
    public static function _hydrate(array $raw, ResourceContext $context): self
    {
        $identifier = Identifier::fromArray($raw, 'an "' . self::TYPE . '" resource object');

        if ($identifier->type !== self::TYPE) {
            throw MalformedDocument::unexpectedMember(
                'The resource object',
                'type',
                '"' . self::TYPE . '" (got "' . $identifier->type . '")',
            );
        }

        return new self($identifier, $raw, $context);
    }

    /**
     * A resource identifier for an `albums` resource that is not in hand, for a relationship write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }

    /**
     * A reusable projection for a write response: `include` and `fields`, and nothing a write would
     * be rejected for carrying.
     *
     * @return AlbumProjection<AlbumBase>
     */
    public static function projection(): AlbumProjection
    {
        return AlbumProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param AlbumCreateShape|null $input
     *
     * @return ($input is null ? AlbumCreateBuilder : AlbumCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `albums` does not have
     */
    public static function create(?array $input = null): AlbumCreateBuilder|AlbumCreate
    {
        return $input === null ? new AlbumCreateBuilder() : AlbumCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param AlbumUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `albums` does not have
     */
    public static function update(?array $input = null): AlbumUpdate
    {
        return $input === null ? new AlbumUpdate() : AlbumUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `artist`
     */
    public function artist(): ?Artist
    {
        $raw = $this->_includedOne('artist', 'artistRef');

        return $raw === null ? null : Artist::_hydrate($raw, $this->_child('artist'));
    }

    /**
     * @return ResourceCollection<Track>
     *
     * @throws RelationNotIncludedException when the read did not include `tracks`
     */
    public function tracks(): ResourceCollection
    {
        $context = $this->_child('tracks');
        $tracks = [];

        foreach ($this->_includedMany('tracks', 'tracksRef') as $raw) {
            $tracks[] = Track::_hydrate($raw, $context);
        }

        return new ResourceCollection($tracks);
    }
}
