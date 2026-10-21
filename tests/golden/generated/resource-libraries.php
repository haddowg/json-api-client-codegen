<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;

/**
 * The `items` relation of a `libraries` resource, hydrated.
 *
 * A marker carries no implementation. `withItems()` intersects it into a query's projection, which
 * is what makes `items()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.LibrariesResource.properties.relationships.items`
 */
interface LibraryHasItems
{
    /**
     * @return ResourceCollection<Track|Album|Artist>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `items`
     */
    public function items(): ResourceCollection;

    /** @var ResourceCollection<Track|Album|Artist> */
    public ResourceCollection $items { get; }
}

/**
 * The `owner` relation of a `libraries` resource, hydrated.
 *
 * A marker carries no implementation. `withOwner()` intersects it into a query's projection, which
 * is what makes `owner()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.LibrariesResource.properties.relationships.owner`
 */
interface LibraryHasOwner
{
    /**
     * The related `public-profiles` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `owner`
     */
    public function owner(): ?PublicProfile;

    public ?PublicProfile $owner { get; }
}

/**
 * A `libraries` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * @generated from `components.schemas.LibrariesResource`
 */
abstract class LibraryBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'libraries';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

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
     * The `items` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function itemsRef(): array
    {
        return $this->_relationship('items', false)->identifiers();
    }

    /**
     * Whether the `items` linkage points at anything. Never throws.
     */
    public function hasItems(): bool
    {
        return !$this->_relationship('items', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'owner'|'items' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, $name === 'owner');
    }
}

/**
 * A `libraries` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `libraries` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @generated from `components.schemas.LibrariesResource`
 */
final class Library extends LibraryBase implements LibraryHasOwner, LibraryHasItems
{
    public ?PublicProfile $owner { get => $this->owner(); }

    /** @var ResourceCollection<Track|Album|Artist> */
    public ResourceCollection $items { get => $this->items(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `libraries` one
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
     * A resource identifier for a `libraries` resource that is not in hand, for a relationship
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
     * @return LibraryProjection<LibraryBase>
     */
    public static function projection(): LibraryProjection
    {
        return LibraryProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param LibraryCreateShape|null $input
     *
     * @return ($input is null ? LibraryCreateBuilder : LibraryCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `libraries` does not have
     */
    public static function create(?array $input = null): LibraryCreateBuilder|LibraryCreate
    {
        return $input === null ? new LibraryCreateBuilder() : LibraryCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param LibraryUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `libraries` does not have
     */
    public static function update(?array $input = null): LibraryUpdate
    {
        return $input === null ? new LibraryUpdate() : LibraryUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `owner`
     */
    public function owner(): ?PublicProfile
    {
        $raw = $this->_includedOne('owner', 'ownerRef');

        return $raw === null ? null : PublicProfile::_hydrate($raw, $this->_child('owner'));
    }

    /**
     * @return ResourceCollection<Track|Album|Artist>
     *
     * @throws RelationNotIncludedException when the read did not include `items`
     */
    public function items(): ResourceCollection
    {
        $context = $this->_child('items');
        $items = [];

        foreach ($this->_includedMany('items', 'itemsRef') as $raw) {
            $items[] = match ($raw['type'] ?? null) {
                Track::TYPE => Track::_hydrate($raw, $context),
                Album::TYPE => Album::_hydrate($raw, $context),
                Artist::TYPE => Artist::_hydrate($raw, $context),
                default => throw MalformedDocument::unexpectedMember('The "items" relationship', 'type', 'one of "tracks", "albums", "artists"'),
            };
        }

        return new ResourceCollection($items);
    }
}
