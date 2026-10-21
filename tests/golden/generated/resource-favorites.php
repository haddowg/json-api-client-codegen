<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;

/**
 * The `user` relation of a `favorites` resource, hydrated.
 *
 * A marker carries no implementation. `withUser()` intersects it into a query's projection, which
 * is what makes `user()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.FavoritesResource.properties.relationships.user`
 */
interface FavoriteHasUser
{
    /**
     * The related `public-profiles` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `user`
     */
    public function user(): ?PublicProfile;

    public ?PublicProfile $user { get; }
}

/**
 * A `favorites` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.FavoritesResource`
 */
abstract class FavoriteBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'favorites';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public \DateTimeImmutable $favoritedAt { get => $this->favoritedAt(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `favoritedAt`
     */
    public function favoritedAt(): \DateTimeImmutable
    {
        return $this->_dateTime('favoritedAt');
    }

    /**
     * The `user` linkage, without hydrating anything. Never throws.
     */
    public function userRef(): ?Identifier
    {
        return $this->_relationship('user', true)->identifier();
    }

    /**
     * Whether the `user` linkage points at anything. Never throws.
     */
    public function hasUser(): bool
    {
        return !$this->_relationship('user', true)->isEmpty();
    }

    /**
     * The `favoritable` linkage, without hydrating anything. Never throws.
     */
    public function favoritableRef(): ?Identifier
    {
        return $this->_relationship('favoritable', true)->identifier();
    }

    /**
     * Whether the `favoritable` linkage points at anything. Never throws.
     */
    public function hasFavoritable(): bool
    {
        return !$this->_relationship('favoritable', true)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'user'|'favoritable' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, true);
    }
}

/**
 * A `favorites` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `favorites` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @generated from `components.schemas.FavoritesResource`
 */
final class Favorite extends FavoriteBase implements FavoriteHasUser
{
    public ?PublicProfile $user { get => $this->user(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `favorites` one
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
     * A resource identifier for a `favorites` resource that is not in hand, for a relationship
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
     * @return FavoriteProjection<FavoriteBase>
     */
    public static function projection(): FavoriteProjection
    {
        return FavoriteProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param FavoriteCreateShape|null $input
     *
     * @return ($input is null ? FavoriteCreateBuilder : FavoriteCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `favorites` does not have
     */
    public static function create(?array $input = null): FavoriteCreateBuilder|FavoriteCreate
    {
        return $input === null ? new FavoriteCreateBuilder() : FavoriteCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param FavoriteUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `favorites` does not have
     */
    public static function update(?array $input = null): FavoriteUpdate
    {
        return $input === null ? new FavoriteUpdate() : FavoriteUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `user`
     */
    public function user(): ?PublicProfile
    {
        $raw = $this->_includedOne('user', 'userRef');

        return $raw === null ? null : PublicProfile::_hydrate($raw, $this->_child('user'));
    }
}
