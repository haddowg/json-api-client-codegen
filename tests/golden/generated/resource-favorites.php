<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Atomic\Created;
use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\NotFound;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\PageLinks;
use haddowg\JsonApiClient\Pagination\PageNumber;
use haddowg\JsonApiClient\Query\Projection;
use haddowg\JsonApiClient\Query\Query;
use haddowg\JsonApiClient\Query\ReadQuery;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Coerce;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\ImmutableConditionable;
use haddowg\JsonApiClient\Support\Missing;
use haddowg\JsonApiClient\Support\QueryConditionable;
use haddowg\JsonApiClient\Writes\CreateInput;
use haddowg\JsonApiClient\Writes\UpdateInput;

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
 * The projection surface of `favorites`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see FavoriteProjection} and {@see FavoriteQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `favorites` read operations
 */
trait FavoriteProjecting
{
    /**
     * Include the `user` relation, narrowing the projection so `$favorite->user` type-checks.
     *
     * @return static<TProj&FavoriteHasUser>
     */
    public function withUser(): static
    {
        return $this->including('user');
    }

    /**
     * Include paths assembled at runtime, or deeper than one hop. Nothing is narrowed, so a
     * relation is reached through its always-safe companions or after a runtime check. The runtime
     * keeps this protected; it is public here because an operation declares `include`.
     *
     * @return static
     */
    public function with(string ...$paths): static
    {
        return parent::with(...$paths);
    }

    /**
     * Sparse fieldsets, with the `favorites` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[favorites]` names a member `favorites` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[FavoriteBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Favorite::FIELDS, true)) {
                throw UnknownFieldException::for(FavoriteBase::TYPE, $field, Favorite::FIELDS);
            }
        }

        return parent::fields($fields);
    }
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

    public ?\DateTimeImmutable $favoritedAt { get => $this->favoritedAt(); }

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
    public function favoritedAt(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('favoritedAt');
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
 * @phpstan-type FavoriteDateInput \DateTimeImmutable|string
 * @phpstan-type FavoriteUserInput Created<PublicProfileBase>|Identifier|PublicProfile|null
 * @phpstan-type FavoriteFavoritableInput Album|Artist|Created<AlbumBase>|Created<ArtistBase>|Created<TrackBase>|Identifier|Track|null
 * @phpstan-type FavoriteCreateShape array{favoritedAt?: FavoriteDateInput, user?: FavoriteUserInput, favoritable?: FavoriteFavoritableInput}
 * @phpstan-type FavoriteUpdateShape array{user?: FavoriteUserInput, favoritable?: FavoriteFavoritableInput}
 * @phpstan-type FavoriteIncludePath 'user'
 * @phpstan-type FavoriteFieldToken 'favoritable'|'favoritedAt'|'user'
 *
 * @generated from `components.schemas.FavoritesResource`
 */
final class Favorite extends FavoriteBase implements FavoriteHasUser
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['favoritedAt', 'user', 'favoritable'];

    /**
     * The members the update document accepts, where they differ from `WRITABLE`.
     *
     * @var list<string>
     */
    public const array WRITABLE_ON_UPDATE = ['user', 'favoritable'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = [];

    /**
     * The members `fields[favorites]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['favoritedAt', 'user', 'favoritable'];

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

/**
 * The canonical create input for `favorites`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type FavoriteCreateShape from Favorite
 *
 * @implements CreateInput<FavoriteBase>
 *
 * @generated from `POST /favorites`
 */
final class FavoriteCreate implements CreateInput
{
    /**
     * @param Created<PublicProfileBase>|Identifier|PublicProfile|Missing|null $user
     * @param Album|Artist|Created<AlbumBase>|Created<ArtistBase>|Created<TrackBase>|Identifier|Track|Missing|null $favoritable
     */
    public function __construct(
        public readonly \DateTimeImmutable|Missing $favoritedAt = Missing::Value,
        public readonly Created|Identifier|PublicProfile|Missing|null $user = Missing::Value,
        public readonly Album|Artist|Created|Identifier|Track|Missing|null $favoritable = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param FavoriteCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `favorites`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Favorite::WRITABLE, true)) {
                throw UnknownAttributeException::for(FavoriteBase::TYPE, (string) $member, Favorite::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            favoritedAt: Coerce::dateTime(\array_key_exists('favoritedAt', $input) ? $input['favoritedAt'] : Missing::Value),
            user: \array_key_exists('user', $input) ? $input['user'] : Missing::Value,
            favoritable: \array_key_exists('favoritable', $input) ? $input['favoritable'] : Missing::Value,
        );
    }

    /**
     * The `POST /favorites` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => FavoriteBase::TYPE];

        $attributes = [];

        if (!$this->favoritedAt instanceof Missing) {
            $attributes['favoritedAt'] = $this->favoritedAt->format(\DateTimeInterface::ATOM);
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->user instanceof Missing) {
            $relationships['user'] = ['data' => Coerce::linkage($this->user)];
        }

        if (!$this->favoritable instanceof Missing) {
            $relationships['favoritable'] = ['data' => Coerce::linkage($this->favoritable)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }

    /**
     * Read the resource a write of this input answered with.
     *
     * @param array<string, mixed> $raw
     */
    public function _hydrate(array $raw, ResourceContext $context): FavoriteBase
    {
        return Favorite::_hydrate($raw, $context);
    }
}

/**
 * The fluent create input for `favorites`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @implements CreateInput<FavoriteBase>
 *
 * @generated from `POST /favorites`
 */
final class FavoriteCreateBuilder implements CreateInput
{
    use Conditionable;

    private \DateTimeImmutable|Missing $favoritedAt = Missing::Value;

    /**
     * @var Created<PublicProfileBase>|Identifier|PublicProfile|Missing|null
     */
    private Created|Identifier|PublicProfile|Missing|null $user = Missing::Value;

    /**
     * @var Album|Artist|Created<AlbumBase>|Created<ArtistBase>|Created<TrackBase>|Identifier|Track|Missing|null
     */
    private Album|Artist|Created|Identifier|Track|Missing|null $favoritable = Missing::Value;

    public function favoritedAt(\DateTimeImmutable $favoritedAt): self
    {
        $this->favoritedAt = $favoritedAt;

        return $this;
    }

    /**
     * @param Created<PublicProfileBase>|Identifier|PublicProfile|null $user
     */
    public function user(Created|Identifier|PublicProfile|null $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Album|Artist|Created<AlbumBase>|Created<ArtistBase>|Created<TrackBase>|Identifier|Track|null $favoritable
     */
    public function favoritable(Album|Artist|Created|Identifier|Track|null $favoritable): self
    {
        $this->favoritable = $favoritable;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     */
    public function build(): FavoriteCreate
    {
        return new FavoriteCreate(
            favoritedAt: $this->favoritedAt,
            user: $this->user,
            favoritable: $this->favoritable,
        );
    }

    /**
     * The `POST /favorites` request document, from the DTO this builds.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        return $this->build()->toDocument();
    }

    /**
     * Read the resource a write of this input answered with.
     *
     * @param array<string, mixed> $raw
     */
    public function _hydrate(array $raw, ResourceContext $context): FavoriteBase
    {
        return Favorite::_hydrate($raw, $context);
    }
}

/**
 * The `favoritable` relationship of one favorite. `get()` reads the linkage and `related()` reads
 * the related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/favorites/{id}/favoritable` and `/favorites/{id}/relationships/favoritable`
 */
final class FavoriteFavoritableRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone: the identifier of the related resource, or null, without fetching it.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no favorite has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "tracks",
     *             "id": "1"
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://music.example/favorites/1/relationships/favoritable",
     *                 "describedby": "/docs.json#/paths/~1favorites~1%7Bid%7D~1relationships~1favoritable/get"
     *             },
     *             "related": {
     *                 "href": "https://music.example/favorites/1/favoritable",
     *                 "describedby": "/docs.json#/paths/~1favorites~1%7Bid%7D~1favoritable/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs.json#/paths/~1favorites~1%7Bid%7D~1relationships~1favoritable/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/favorites/' . $this->id . '/relationships/favoritable'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "favoritable" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no favorite has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): Album|Artist|Track|null
    {
        $context = $this->fetcher->read(Request::get('/favorites/' . $this->id . '/favoritable'));
        $raw = $context->primary();

        return $raw === null ? null : self::member($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no favorite has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "tracks",
     *             "id": "1"
     *         }
     *     }
     */
    public function set(Album|Artist|Identifier|Track|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/favorites/' . $this->id . '/relationships/favoritable',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * The related resource as the class its own `type` names.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource is of a type the relation does not declare
     */
    private static function member(array $raw, ResourceContext $context): Album|Artist|Track
    {
        return match ($raw['type'] ?? null) {
            'tracks' => Track::_hydrate($raw, $context),
            'albums' => Album::_hydrate($raw, $context),
            'artists' => Artist::_hydrate($raw, $context),
            default => throw MalformedDocument::unexpectedMember('The related resource', 'type', 'one of "tracks", "albums", "artists"'),
        };
    }
}

/**
 * One favorite by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type FavoriteUpdateShape from Favorite
 *
 * @generated from the `favorites` operations on one resource
 */
final class FavoriteHandle
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The id this handle addresses.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Fetch the favorite, shaped by the optional projection: a closure over a fresh projection, or
     * a prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(FavoriteProjection<FavoriteBase>): FavoriteProjection<TProj>)|FavoriteProjection<TProj>|null $projection
     *
     * @return ($projection is null ? FavoriteBase : FavoriteBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no favorite has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|FavoriteProjection|null $projection = null): FavoriteBase
    {
        $query = FavoriteProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/favorites/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The favorites response', 'data');

        /** @var FavoriteBase&TProj */
        return $context->hydrate($raw, Favorite::_hydrate(...));
    }

    /**
     * Patch the favorite, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param FavoriteUpdate|FavoriteUpdateShape                                                                     $input
     * @param (callable(FavoriteProjection<FavoriteBase>): FavoriteProjection<TProj>)|FavoriteProjection<TProj>|null $projection
     *
     * @return ($projection is null ? FavoriteBase : FavoriteBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no favorite has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "favorites",
     *             "id": "1",
     *             "relationships": {
     *                 "user": {
     *                     "data": {
     *                         "type": "public-profiles",
     *                         "id": "1"
     *                     }
     *                 },
     *                 "favoritable": {
     *                     "data": {
     *                         "type": "tracks",
     *                         "id": "1"
     *                     }
     *                 }
     *             }
     *         }
     *     }
     */
    public function update(
        FavoriteUpdate|array $input,
        callable|FavoriteProjection|null $projection = null,
    ): FavoriteBase {
        $document = match (true) {
            $input instanceof FavoriteUpdate => $input,
            default => FavoriteUpdate::from($input),
        };

        $query = FavoriteProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/favorites/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var FavoriteBase&TProj */
        return $context->hydrate($raw, Favorite::_hydrate(...));
    }

    /**
     * Delete the favorite.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no favorite has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/favorites/' . $this->id));
    }

    /**
     * The `favoritable` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function favoritable(): FavoriteFavoritableRelation
    {
        return new FavoriteFavoritableRelation($this->fetcher, $this->id);
    }

    /**
     * The `user` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function user(): FavoriteUserRelation
    {
        return new FavoriteUserRelation($this->fetcher, $this->id);
    }
}

/**
 * What a response carrying `favorites` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /favorites/{id}`, `POST /favorites`, `PATCH /favorites/{id}`
 */
final class FavoriteProjection extends Projection
{
    /** @use FavoriteProjecting<TProj> */
    use FavoriteProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<FavoriteBase>
     */
    public static function make(): self
    {
        /** @var self<FavoriteBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<FavoriteBase>): self<T>)|self<T>|null $projection
     */
    public static function resolve(callable|self|null $projection): ReadQuery
    {
        if ($projection === null) {
            return new ReadQuery();
        }

        return ($projection instanceof self ? $projection : $projection(self::make()))->toReadQuery();
    }
}

/**
 * A read of `GET /favorites`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type FavoriteIncludePath from Favorite
 *
 * @generated from `GET /favorites`
 */
final class FavoriteQuery extends Query
{
    /** @use FavoriteProjecting<TProj> */
    use FavoriteProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * `$number` defaults to 1 and `$size` defaults to 15.
     *
     * @param positive-int    $number
     * @param int<1, 50>|null $size
     *
     * @return static
     */
    public function page(int $number, ?int $size = null): static
    {
        return $this->paging($size === null ? ['number' => $number] : ['number' => $number, 'size' => $size]);
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /favorites` is paginated;
     * the runtime keeps it protected.
     *
     * @param array<string, mixed> $page
     *
     * @return static
     */
    public function pageRaw(array $page): static
    {
        return parent::pageRaw($page);
    }

    /**
     * Run the read.
     *
     * @return PaginatedCollection<TProj, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query), $query);
    }

    /**
     * The first favorite the read matches, fetching a single-resource page rather than a whole one.
     *
     * @return TProj|null
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function first(): ?object
    {
        return $this->page(1, 1)->get()->first();
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/favorites'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $favorites = [];

        foreach ($context->primaryList() as $raw) {
            $favorites[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $favorites,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Favorite`, but
     * `TProj` is a template parameter no bound can prove it satisfies. `ResourceContext::hydrate()`
     * erases the concrete class so the assertion is legal.
     *
     * @param array<string, mixed> $raw
     *
     * @return TProj
     */
    private function hydrate(array $raw, ResourceContext $context): object
    {
        /** @var TProj */
        return $context->hydrate($raw, Favorite::_hydrate(...));
    }
}

/**
 * The canonical update input for `favorites`: every member optional, which is what a JSON:API
 * `PATCH` is. The id is not a member; it comes from the handle the update is called on, so it
 * cannot disagree with the URL.
 *
 * @phpstan-import-type FavoriteUpdateShape from Favorite
 *
 * @implements UpdateInput<FavoriteBase>
 *
 * @generated from `PATCH /favorites/{id}`
 */
final class FavoriteUpdate implements UpdateInput
{
    use ImmutableConditionable;

    /**
     * @param Created<PublicProfileBase>|Identifier|PublicProfile|Missing|null $user
     * @param Album|Artist|Created<AlbumBase>|Created<ArtistBase>|Created<TrackBase>|Identifier|Track|Missing|null $favoritable
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly Created|Identifier|PublicProfile|Missing|null $user = Missing::Value,
        public readonly Album|Artist|Created|Identifier|Track|Missing|null $favoritable = Missing::Value,
    ) {}

    /**
     * @param Created<PublicProfileBase>|Identifier|PublicProfile|null $user
     *
     * @phpstan-pure
     */
    public function user(Created|Identifier|PublicProfile|null $user): self
    {
        return new self(
            user: $user,
            favoritable: $this->favoritable,
        );
    }

    /**
     * @param Album|Artist|Created<AlbumBase>|Created<ArtistBase>|Created<TrackBase>|Identifier|Track|null $favoritable
     *
     * @phpstan-pure
     */
    public function favoritable(Album|Artist|Created|Identifier|Track|null $favoritable): self
    {
        return new self(
            user: $this->user,
            favoritable: $favoritable,
        );
    }

    /**
     * The input itself, so an update assembled fluently ends the way a create one does.
     *
     * @phpstan-pure
     */
    public function build(): self
    {
        return $this;
    }

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param FavoriteUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `favorites`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Favorite::WRITABLE_ON_UPDATE, true)) {
                throw UnknownAttributeException::for(FavoriteBase::TYPE, (string) $member, Favorite::WRITABLE_ON_UPDATE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            user: \array_key_exists('user', $input) ? $input['user'] : Missing::Value,
            favoritable: \array_key_exists('favoritable', $input) ? $input['favoritable'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /favorites/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => FavoriteBase::TYPE, 'id' => $id];

        $relationships = [];

        if (!$this->user instanceof Missing) {
            $relationships['user'] = ['data' => Coerce::linkage($this->user)];
        }

        if (!$this->favoritable instanceof Missing) {
            $relationships['favoritable'] = ['data' => Coerce::linkage($this->favoritable)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }

    /**
     * Read the resource a write of this input answered with.
     *
     * @param array<string, mixed> $raw
     */
    public function _hydrate(array $raw, ResourceContext $context): FavoriteBase
    {
        return Favorite::_hydrate($raw, $context);
    }
}

/**
 * The `user` relationship of one favorite. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/favorites/{id}/user` and `/favorites/{id}/relationships/user`
 */
final class FavoriteUserRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone: the identifier of the related resource, or null, without fetching it.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no favorite has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "public-profiles",
     *             "id": "1"
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://music.example/favorites/1/relationships/user",
     *                 "describedby": "/docs.json#/paths/~1favorites~1%7Bid%7D~1relationships~1user/get"
     *             },
     *             "related": {
     *                 "href": "https://music.example/favorites/1/user",
     *                 "describedby": "/docs.json#/paths/~1favorites~1%7Bid%7D~1user/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs.json#/paths/~1favorites~1%7Bid%7D~1relationships~1user/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/favorites/' . $this->id . '/relationships/user'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "user" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no favorite has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "public-profiles",
     *             "id": "1",
     *             "links": {
     *                 "self": {
     *                     "href": "https://music.example/public-profiles/1",
     *                     "describedby": "/docs.json#/paths/~1public-profiles~1%7Bid%7D/get"
     *                 }
     *             },
     *             "attributes": {
     *                 "displayName": "Ada"
     *             }
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://music.example/favorites/1/user",
     *                 "describedby": "/docs.json#/paths/~1favorites~1%7Bid%7D~1user/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs.json#/paths/~1favorites~1%7Bid%7D~1user/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function related(): ?PublicProfile
    {
        $context = $this->fetcher->read(Request::get('/favorites/' . $this->id . '/user'));
        $raw = $context->primary();

        return $raw === null ? null : PublicProfile::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no favorite has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "public-profiles",
     *             "id": "1"
     *         }
     *     }
     */
    public function set(Identifier|PublicProfile|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/favorites/' . $this->id . '/relationships/user',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * The `favorites` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type FavoriteCreateShape from Favorite
 *
 * @generated from the `favorites` operations
 */
final class Favorites
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the favorites collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return FavoriteQuery<FavoriteBase>
     */
    public function query(): FavoriteQuery
    {
        /** @var FavoriteQuery<FavoriteBase> */
        return new FavoriteQuery($this->fetcher);
    }

    /**
     * A handle on one favorite by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): FavoriteHandle
    {
        return new FavoriteHandle($this->fetcher, $id);
    }

    /**
     * Create a favorite, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param FavoriteCreate|FavoriteCreateBuilder|FavoriteCreateShape                                               $input
     * @param (callable(FavoriteProjection<FavoriteBase>): FavoriteProjection<TProj>)|FavoriteProjection<TProj>|null $projection
     *
     * @return ($projection is null ? FavoriteBase : FavoriteBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the create is not permitted
     * @throws NotFound             when the endpoint does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document conflicts with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "favorites",
     *             "attributes": {
     *                 "favoritedAt": "2024-06-01T09:30:00+00:00"
     *             },
     *             "relationships": {
     *                 "user": {
     *                     "data": {
     *                         "type": "public-profiles",
     *                         "id": "1"
     *                     }
     *                 },
     *                 "favoritable": {
     *                     "data": {
     *                         "type": "tracks",
     *                         "id": "1"
     *                     }
     *                 }
     *             }
     *         }
     *     }
     */
    public function create(
        FavoriteCreate|FavoriteCreateBuilder|array $input,
        callable|FavoriteProjection|null $projection = null,
    ): FavoriteBase {
        $document = match (true) {
            $input instanceof FavoriteCreate => $input,
            $input instanceof FavoriteCreateBuilder => $input->build(),
            default => FavoriteCreate::from($input),
        };

        $query = FavoriteProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/favorites'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var FavoriteBase&TProj */
        return $context->hydrate($raw, Favorite::_hydrate(...));
    }
}
