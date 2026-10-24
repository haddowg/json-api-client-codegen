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
 * An `artists` resource with no relation hydrated.
 *
 * Trimmed to what the `albums` surface reaches: the attributes and the `albums` relation. A
 * full run emits the query builder, write shapes, accessor and handle for this type too.
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

    /** @var non-empty-string */
    public string $name { get => $this->name(); }

    public string $slug { get => $this->slug(); }

    public ?string $website { get => $this->website(); }

    public ?string $bio { get => $this->bio(); }

    public int $trackCount { get => $this->trackCount(); }

    public ?\DateTimeImmutable $createdAt { get => $this->createdAt(); }

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
     * At most 120 characters.
     *
     * @return non-empty-string
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('name');

        return $value;
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `slug`
     */
    public function slug(): string
    {
        return $this->_string('slug');
    }

    /**
     * In `uri` format.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `website`
     */
    public function website(): ?string
    {
        return $this->_nullableString('website');
    }

    /**
     * At most 1000 characters.
     *
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
    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('createdAt');
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
 * An `artists` resource, trimmed to what the `albums` surface reaches.
 *
 * @generated from `components.schemas.ArtistsResource`
 */
final class Artist extends ArtistBase implements ArtistHasAlbums
{
    /** @var ResourceCollection<Album> */
    public ResourceCollection $albums { get => $this->albums(); }

    /**
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
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }

    /**
     * @return ResourceCollection<Album>
     *
     * @throws RelationNotIncludedException when the read did not include `albums` at this depth
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
