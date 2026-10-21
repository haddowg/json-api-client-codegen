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
 * The `orderedTracks` relation of a `playlists` resource, hydrated.
 *
 * A marker carries no implementation. `withOrderedTracks()` intersects it into a query's
 * projection, which is what makes `orderedTracks()` type-check after that include and fail without
 * it.
 *
 * @generated from `components.schemas.PlaylistsResource.properties.relationships.orderedTracks`
 */
interface PlaylistHasOrderedTracks
{
    /**
     * @return ResourceCollection<Track>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `orderedTracks`
     */
    public function orderedTracks(): ResourceCollection;

    /** @var ResourceCollection<Track> */
    public ResourceCollection $orderedTracks { get; }
}

/**
 * The `publicOwner` relation of a `playlists` resource, hydrated.
 *
 * A marker carries no implementation. `withPublicOwner()` intersects it into a query's projection,
 * which is what makes `publicOwner()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.PlaylistsResource.properties.relationships.publicOwner`
 */
interface PlaylistHasPublicOwner
{
    /**
     * The related `public-profiles` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `publicOwner`
     */
    public function publicOwner(): ?PublicProfile;

    public ?PublicProfile $publicOwner { get; }
}

/**
 * The `tracks` relation of a `playlists` resource, hydrated.
 *
 * A marker carries no implementation. `withTracks()` intersects it into a query's projection, which
 * is what makes `tracks()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.PlaylistsResource.properties.relationships.tracks`
 */
interface PlaylistHasTracks
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
 * A `playlists` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.PlaylistsResource`
 */
abstract class PlaylistBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'playlists';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $title { get => $this->title(); }

    public string $slug { get => $this->slug(); }

    public bool $public { get => $this->public(); }

    public ?string $externalId { get => $this->externalId(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `slug`
     */
    public function slug(): string
    {
        return $this->_string('slug');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `public`
     */
    public function public(): bool
    {
        return $this->_bool('public');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `externalId`
     */
    public function externalId(): ?string
    {
        return $this->_nullableString('externalId');
    }

    /**
     * The `owner` linkage, without hydrating anything. Never throws.
     */
    public function ownerRef(): ?Identifier
    {
        return $this->_relationship('owner', true)->identifier();
    }

    /**
     * Whether the `owner` linkage points at anything. Never throws.
     */
    public function hasOwner(): bool
    {
        return !$this->_relationship('owner', true)->isEmpty();
    }

    /**
     * The `publicOwner` linkage, without hydrating anything. Never throws.
     */
    public function publicOwnerRef(): ?Identifier
    {
        return $this->_relationship('publicOwner', true)->identifier();
    }

    /**
     * Whether the `publicOwner` linkage points at anything. Never throws.
     */
    public function hasPublicOwner(): bool
    {
        return !$this->_relationship('publicOwner', true)->isEmpty();
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
     * The `orderedTracks` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function orderedTracksRef(): array
    {
        return $this->_relationship('orderedTracks', false)->identifiers();
    }

    /**
     * Whether the `orderedTracks` linkage points at anything. Never throws.
     */
    public function hasOrderedTracks(): bool
    {
        return !$this->_relationship('orderedTracks', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'owner'|'publicOwner'|'tracks'|'orderedTracks' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, \in_array($name, ['owner', 'publicOwner'], true));
    }
}

/**
 * A `playlists` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `playlists` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @generated from `components.schemas.PlaylistsResource`
 */
final class Playlist extends PlaylistBase implements PlaylistHasPublicOwner, PlaylistHasTracks, PlaylistHasOrderedTracks
{
    public ?PublicProfile $publicOwner { get => $this->publicOwner(); }

    /** @var ResourceCollection<Track> */
    public ResourceCollection $tracks { get => $this->tracks(); }

    /** @var ResourceCollection<Track> */
    public ResourceCollection $orderedTracks { get => $this->orderedTracks(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `playlists` one
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
     * A resource identifier for a `playlists` resource that is not in hand, for a relationship
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
     * @return PlaylistProjection<PlaylistBase>
     */
    public static function projection(): PlaylistProjection
    {
        return PlaylistProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param PlaylistCreateShape|null $input
     *
     * @return ($input is null ? PlaylistCreateBuilder : PlaylistCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `playlists` does not have
     */
    public static function create(?array $input = null): PlaylistCreateBuilder|PlaylistCreate
    {
        return $input === null ? new PlaylistCreateBuilder() : PlaylistCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param PlaylistUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `playlists` does not have
     */
    public static function update(?array $input = null): PlaylistUpdate
    {
        return $input === null ? new PlaylistUpdate() : PlaylistUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `publicOwner`
     */
    public function publicOwner(): ?PublicProfile
    {
        $raw = $this->_includedOne('publicOwner', 'publicOwnerRef');

        return $raw === null ? null : PublicProfile::_hydrate($raw, $this->_child('publicOwner'));
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

    /**
     * @return ResourceCollection<Track>
     *
     * @throws RelationNotIncludedException when the read did not include `orderedTracks`
     */
    public function orderedTracks(): ResourceCollection
    {
        $context = $this->_child('orderedTracks');
        $orderedTracks = [];

        foreach ($this->_includedMany('orderedTracks', 'orderedTracksRef') as $raw) {
            $orderedTracks[] = Track::_hydrate($raw, $context);
        }

        return new ResourceCollection($orderedTracks);
    }
}
