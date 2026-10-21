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
 * The `albums` relation of an `artists` resource, hydrated.
 *
 * A marker carries no implementation. `withAlbums()` intersects it into a query's projection, which
 * is what makes `albums()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.ArtistsResource.properties.relationships.albums`
 */
interface ArtistHasAlbums
{
    /**
     * @return ResourceCollection<Album>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `albums`
     */
    public function albums(): ResourceCollection;

    /** @var ResourceCollection<Album> */
    public ResourceCollection $albums { get; }
}

/**
 * An `artists` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.ArtistsResource`
 */
abstract class ArtistBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'artists';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $name { get => $this->name(); }

    public string $slug { get => $this->slug(); }

    public ?string $website { get => $this->website(); }

    public ?string $bio { get => $this->bio(); }

    public int $trackCount { get => $this->trackCount(); }

    public \DateTimeImmutable $createdAt { get => $this->createdAt(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        return $this->_string('name');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `slug`
     */
    public function slug(): string
    {
        return $this->_string('slug');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `website`
     */
    public function website(): ?string
    {
        return $this->_nullableString('website');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `bio`
     */
    public function bio(): ?string
    {
        return $this->_nullableString('bio');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `trackCount`
     */
    public function trackCount(): int
    {
        return $this->_int('trackCount');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `createdAt`
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->_dateTime('createdAt');
    }

    /**
     * The `albums` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function albumsRef(): array
    {
        return $this->_relationship('albums', false)->identifiers();
    }

    /**
     * Whether the `albums` linkage points at anything. Never throws.
     */
    public function hasAlbums(): bool
    {
        return !$this->_relationship('albums', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'albums' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, false);
    }
}

/**
 * An `artists` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `artists` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @generated from `components.schemas.ArtistsResource`
 */
final class Artist extends ArtistBase implements ArtistHasAlbums
{
    /** @var ResourceCollection<Album> */
    public ResourceCollection $albums { get => $this->albums(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `artists` one
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
     * A resource identifier for an `artists` resource that is not in hand, for a relationship
     * write.
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
     * @return ArtistProjection<ArtistBase>
     */
    public static function projection(): ArtistProjection
    {
        return ArtistProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param ArtistCreateShape|null $input
     *
     * @return ($input is null ? ArtistCreateBuilder : ArtistCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `artists` does not have
     */
    public static function create(?array $input = null): ArtistCreateBuilder|ArtistCreate
    {
        return $input === null ? new ArtistCreateBuilder() : ArtistCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param ArtistUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `artists` does not have
     */
    public static function update(?array $input = null): ArtistUpdate
    {
        return $input === null ? new ArtistUpdate() : ArtistUpdate::from($input);
    }

    /**
     * @return ResourceCollection<Album>
     *
     * @throws RelationNotIncludedException when the read did not include `albums`
     */
    public function albums(): ResourceCollection
    {
        $context = $this->_child('albums');
        $albums = [];

        foreach ($this->_includedMany('albums', 'albumsRef') as $raw) {
            $albums[] = Album::_hydrate($raw, $context);
        }

        return new ResourceCollection($albums);
    }
}
