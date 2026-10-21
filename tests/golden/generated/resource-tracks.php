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
 * The `album` relation of a `tracks` resource, hydrated.
 *
 * A marker carries no implementation. `withAlbum()` intersects it into a query's projection, which
 * is what makes `album()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.TracksResource.properties.relationships.album`
 */
interface TrackHasAlbum
{
    /**
     * The related `albums` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `album`
     */
    public function album(): ?Album;

    public ?Album $album { get; }
}

/**
 * The `playlists` relation of a `tracks` resource, hydrated.
 *
 * A marker carries no implementation. `withPlaylists()` intersects it into a query's projection,
 * which is what makes `playlists()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.TracksResource.properties.relationships.playlists`
 */
interface TrackHasPlaylists
{
    /**
     * @return ResourceCollection<Playlist>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `playlists`
     */
    public function playlists(): ResourceCollection;

    /** @var ResourceCollection<Playlist> */
    public ResourceCollection $playlists { get; }
}

/**
 * A `tracks` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.TracksResource`
 */
abstract class TrackBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'tracks';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $title { get => $this->title(); }

    public int $trackNumber { get => $this->trackNumber(); }

    public int $durationSeconds { get => $this->durationSeconds(); }

    public bool $explicit { get => $this->explicit(); }

    /** @var list<string> */
    public array $genres { get => $this->genres(); }

    public ?string $previewOffset { get => $this->previewOffset(); }

    public string $displayTitle { get => $this->displayTitle(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `trackNumber`
     */
    public function trackNumber(): int
    {
        return $this->_int('trackNumber');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `durationSeconds`
     */
    public function durationSeconds(): int
    {
        return $this->_int('durationSeconds');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `explicit`
     */
    public function explicit(): bool
    {
        return $this->_bool('explicit');
    }

    /**
     * @return list<string>
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `genres`
     */
    public function genres(): array
    {
        return $this->_stringList('genres');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `previewOffset`
     */
    public function previewOffset(): ?string
    {
        return $this->_nullableString('previewOffset');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `displayTitle`
     */
    public function displayTitle(): string
    {
        return $this->_string('displayTitle');
    }

    /**
     * The `album` linkage, without hydrating anything. Never throws.
     */
    public function albumRef(): ?Identifier
    {
        return $this->_relationship('album', true)->identifier();
    }

    /**
     * Whether the `album` linkage points at anything. Never throws.
     */
    public function hasAlbum(): bool
    {
        return !$this->_relationship('album', true)->isEmpty();
    }

    /**
     * The `playlists` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function playlistsRef(): array
    {
        return $this->_relationship('playlists', false)->identifiers();
    }

    /**
     * Whether the `playlists` linkage points at anything. Never throws.
     */
    public function hasPlaylists(): bool
    {
        return !$this->_relationship('playlists', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'album'|'playlists' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, $name === 'album');
    }
}

/**
 * A `tracks` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `tracks` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @generated from `components.schemas.TracksResource`
 */
final class Track extends TrackBase implements TrackHasAlbum, TrackHasPlaylists
{
    public ?Album $album { get => $this->album(); }

    /** @var ResourceCollection<Playlist> */
    public ResourceCollection $playlists { get => $this->playlists(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `tracks` one
     */
    public static function _hydrate(array $raw, ResourceContext $context): self
    {
        $identifier = Identifier::fromArray($raw, 'a "' . self::TYPE . '" resource object');

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
     * A resource identifier for a `tracks` resource that is not in hand, for a relationship write.
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
     * @return TrackProjection<TrackBase>
     */
    public static function projection(): TrackProjection
    {
        return TrackProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param TrackCreateShape|null $input
     *
     * @return ($input is null ? TrackCreateBuilder : TrackCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `tracks` does not have
     */
    public static function create(?array $input = null): TrackCreateBuilder|TrackCreate
    {
        return $input === null ? new TrackCreateBuilder() : TrackCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param TrackUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `tracks` does not have
     */
    public static function update(?array $input = null): TrackUpdate
    {
        return $input === null ? new TrackUpdate() : TrackUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `album`
     */
    public function album(): ?Album
    {
        $raw = $this->_includedOne('album', 'albumRef');

        return $raw === null ? null : Album::_hydrate($raw, $this->_child('album'));
    }

    /**
     * @return ResourceCollection<Playlist>
     *
     * @throws RelationNotIncludedException when the read did not include `playlists`
     */
    public function playlists(): ResourceCollection
    {
        $context = $this->_child('playlists');
        $playlists = [];

        foreach ($this->_includedMany('playlists', 'playlistsRef') as $raw) {
            $playlists[] = Playlist::_hydrate($raw, $context);
        }

        return new ResourceCollection($playlists);
    }
}
