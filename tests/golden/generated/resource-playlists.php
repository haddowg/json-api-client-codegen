<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
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
 * The projection surface of `playlists`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see PlaylistProjection} and {@see PlaylistQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `playlists` read operations
 */
trait PlaylistProjecting
{
    /**
     * Include the `publicOwner` relation, narrowing the projection so `$playlist->publicOwner`
     * type-checks.
     *
     * @return static<TProj&PlaylistHasPublicOwner>
     */
    public function withPublicOwner(): static
    {
        return $this->including('publicOwner');
    }

    /**
     * Include the `tracks` relation, narrowing the projection so `$playlist->tracks` type-checks.
     *
     * @return static<TProj&PlaylistHasTracks>
     */
    public function withTracks(): static
    {
        return $this->including('tracks');
    }

    /**
     * Include the `orderedTracks` relation, narrowing the projection so `$playlist->orderedTracks`
     * type-checks.
     *
     * @return static<TProj&PlaylistHasOrderedTracks>
     */
    public function withOrderedTracks(): static
    {
        return $this->including('orderedTracks');
    }

    /**
     * Sparse fieldsets, with the `playlists` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[playlists]` names a member `playlists` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[PlaylistBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Playlist::FIELDS, true)) {
                throw UnknownFieldException::for(PlaylistBase::TYPE, $field, Playlist::FIELDS);
            }
        }

        return parent::fields($fields);
    }
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
 * @phpstan-type PlaylistOwnerInput Identifier|null
 * @phpstan-type PlaylistPublicOwnerInput Identifier|PublicProfile|null
 * @phpstan-type PlaylistTracksInput list<Identifier|Track>
 * @phpstan-type PlaylistOrderedTracksInput list<Identifier|Track>
 * @phpstan-type PlaylistCreateShape array{title: string, public?: bool, externalId?: string|null, owner?: PlaylistOwnerInput, publicOwner?: PlaylistPublicOwnerInput, tracks?: PlaylistTracksInput, orderedTracks?: PlaylistOrderedTracksInput}
 * @phpstan-type PlaylistUpdateShape array{title?: string, public?: bool, externalId?: string|null, owner?: PlaylistOwnerInput, publicOwner?: PlaylistPublicOwnerInput, tracks?: PlaylistTracksInput, orderedTracks?: PlaylistOrderedTracksInput}
 * @phpstan-type PlaylistIncludePath 'orderedTracks'|'orderedTracks.album'|'orderedTracks.playlists'|'publicOwner'|'tracks'|'tracks.album'|'tracks.playlists'
 * @phpstan-type PlaylistFieldToken 'externalId'|'orderedTracks'|'owner'|'public'|'publicOwner'|'slug'|'title'|'tracks'
 * @phpstan-type PlaylistCountToken 'tracks'
 *
 * @generated from `components.schemas.PlaylistsResource`
 */
final class Playlist extends PlaylistBase implements PlaylistHasPublicOwner, PlaylistHasTracks, PlaylistHasOrderedTracks
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['title', 'public', 'externalId', 'owner', 'publicOwner', 'tracks', 'orderedTracks'];

    /**
     * Members a create document requires. Read-only attributes (`slug`) are in no write shape.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['title'];

    /**
     * The members `fields[playlists]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = [
        'title',
        'slug',
        'public',
        'externalId',
        'owner',
        'publicOwner',
        'tracks',
        'orderedTracks',
    ];

    /**
     * The `withCount` tokens `GET /playlists` advertises.
     *
     * @var list<string>
     */
    public const array COUNT_TOKENS = ['tracks'];

    /**
     * The Countable profile, which the client adds to `Accept` only when a request uses
     * `withCount`.
     */
    public const string COUNTABLE_PROFILE = 'https://haddowg.github.io/json-api/profiles/countable/';

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

/**
 * The canonical create input for `playlists`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type PlaylistCreateShape from Playlist
 *
 * @generated from `POST /playlists`
 */
final class PlaylistCreate
{
    /**
     * @param Identifier|Missing|null $owner
     * @param Identifier|PublicProfile|Missing|null $publicOwner
     * @param list<Identifier|Track>|Missing $tracks
     * @param list<Identifier|Track>|Missing $orderedTracks
     */
    public function __construct(
        public readonly string $title,
        public readonly bool|Missing $public = Missing::Value,
        public readonly string|Missing|null $externalId = Missing::Value,
        public readonly Identifier|Missing|null $owner = Missing::Value,
        public readonly Identifier|PublicProfile|Missing|null $publicOwner = Missing::Value,
        public readonly array|Missing $tracks = Missing::Value,
        public readonly array|Missing $orderedTracks = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param PlaylistCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `playlists`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Playlist::WRITABLE, true)) {
                throw UnknownAttributeException::for(PlaylistBase::TYPE, (string) $member, Playlist::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            title: $input['title'],
            public: \array_key_exists('public', $input) ? $input['public'] : Missing::Value,
            externalId: \array_key_exists('externalId', $input) ? $input['externalId'] : Missing::Value,
            owner: \array_key_exists('owner', $input) ? $input['owner'] : Missing::Value,
            publicOwner: \array_key_exists('publicOwner', $input) ? $input['publicOwner'] : Missing::Value,
            tracks: \array_key_exists('tracks', $input) ? $input['tracks'] : Missing::Value,
            orderedTracks: \array_key_exists('orderedTracks', $input) ? $input['orderedTracks'] : Missing::Value,
        );
    }

    /**
     * The `POST /playlists` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => PlaylistBase::TYPE];

        $attributes = ['title' => $this->title];

        if (!$this->public instanceof Missing) {
            $attributes['public'] = $this->public;
        }

        if (!$this->externalId instanceof Missing) {
            $attributes['externalId'] = $this->externalId;
        }

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->owner instanceof Missing) {
            $relationships['owner'] = ['data' => Coerce::linkage($this->owner)];
        }

        if (!$this->publicOwner instanceof Missing) {
            $relationships['publicOwner'] = ['data' => Coerce::linkage($this->publicOwner)];
        }

        if (!$this->tracks instanceof Missing) {
            $relationships['tracks'] = ['data' => Coerce::linkageList($this->tracks)];
        }

        if (!$this->orderedTracks instanceof Missing) {
            $relationships['orderedTracks'] = ['data' => Coerce::linkageList($this->orderedTracks)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `playlists`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /playlists`
 */
final class PlaylistCreateBuilder
{
    use Conditionable;

    private string|Missing $title = Missing::Value;

    private bool|Missing $public = Missing::Value;

    private string|Missing|null $externalId = Missing::Value;

    private Identifier|Missing|null $owner = Missing::Value;

    private Identifier|PublicProfile|Missing|null $publicOwner = Missing::Value;

    /**
     * @var list<Identifier|Track>|Missing
     */
    private array|Missing $tracks = Missing::Value;

    /**
     * @var list<Identifier|Track>|Missing
     */
    private array|Missing $orderedTracks = Missing::Value;

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function public(bool $public = true): self
    {
        $this->public = $public;

        return $this;
    }

    public function externalId(?string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function owner(?Identifier $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function publicOwner(Identifier|PublicProfile|null $publicOwner): self
    {
        $this->publicOwner = $publicOwner;

        return $this;
    }

    /**
     * @param list<Identifier|Track> $tracks
     */
    public function tracks(array $tracks): self
    {
        $this->tracks = $tracks;

        return $this;
    }

    /**
     * @param list<Identifier|Track> $orderedTracks
     */
    public function orderedTracks(array $orderedTracks): self
    {
        $this->orderedTracks = $orderedTracks;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): PlaylistCreate
    {
        if ($this->title instanceof Missing) {
            throw MissingRequiredMemberException::for(PlaylistBase::TYPE, ['title']);
        }

        return new PlaylistCreate(
            title: $this->title,
            public: $this->public,
            externalId: $this->externalId,
            owner: $this->owner,
            publicOwner: $this->publicOwner,
            tracks: $this->tracks,
            orderedTracks: $this->orderedTracks,
        );
    }
}

/**
 * What a response carrying `playlists` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /playlists/{id}`, `POST /playlists`, `PATCH /playlists/{id}`
 */
final class PlaylistProjection extends Projection
{
    /** @use PlaylistProjecting<TProj> */
    use PlaylistProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<PlaylistBase>
     */
    public static function make(): self
    {
        /** @var self<PlaylistBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<PlaylistBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /playlists`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not. Filters come in three doors:
 * a `whereX()` per filter, the typed `filter()` shape, and the loose `filterRaw()`. They are
 * separate methods because a parameter typed as the shape or any array would accept anything. Both
 * array doors still check names at runtime.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type PlaylistCountToken from Playlist
 * @phpstan-import-type PlaylistIncludePath from Playlist
 *
 * @generated from `GET /playlists`
 */
final class PlaylistQuery extends Query
{
    /** @use PlaylistProjecting<TProj> */
    use PlaylistProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Count related resources without including them, under the Countable profile. `GET /playlists`
     * advertises `tracks`. A counted relation is still not an included one: its count is read
     * through `_rel()`.
     *
     * @param list<PlaylistCountToken> $tokens
     *
     * @return static
     */
    public function withCount(array $tokens): static
    {
        return $this->counting($tokens);
    }

    /**
     * @param positive-int      $number
     * @param positive-int|null $size
     *
     * @return static
     */
    public function page(int $number, ?int $size = null): static
    {
        return $this->paging($size === null ? ['number' => $number] : ['number' => $number, 'size' => $size]);
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
     * The first playlist the read matches, fetching a single-resource page rather than a whole one.
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

    /**
     * The Countable profile is negotiated only when a request actually uses a `withCount`
     * parameter, which is what `x-profile` on that parameter states.
     */
    private function request(ReadQuery $query): Request
    {
        $request = Request::get($query->appendTo('/playlists'));

        return $query->withCount === [] ? $request : $request->withProfiles([Playlist::COUNTABLE_PROFILE]);
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $playlists = [];

        foreach ($context->primaryList() as $raw) {
            $playlists[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $playlists,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Playlist`, but
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
        return $context->hydrate($raw, Playlist::_hydrate(...));
    }
}

/**
 * The canonical update input for `playlists`: every member optional, which is what a JSON:API
 * `PATCH` is. The id is not a member; it comes from the handle the update is called on, so it
 * cannot disagree with the URL.
 *
 * @phpstan-import-type PlaylistUpdateShape from Playlist
 *
 * @generated from `PATCH /playlists/{id}`
 */
final class PlaylistUpdate
{
    use ImmutableConditionable;

    /**
     * @param Identifier|Missing|null $owner
     * @param Identifier|PublicProfile|Missing|null $publicOwner
     * @param list<Identifier|Track>|Missing $tracks
     * @param list<Identifier|Track>|Missing $orderedTracks
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $title = Missing::Value,
        public readonly bool|Missing $public = Missing::Value,
        public readonly string|Missing|null $externalId = Missing::Value,
        public readonly Identifier|Missing|null $owner = Missing::Value,
        public readonly Identifier|PublicProfile|Missing|null $publicOwner = Missing::Value,
        public readonly array|Missing $tracks = Missing::Value,
        public readonly array|Missing $orderedTracks = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function title(string $title): self
    {
        return new self(
            title: $title,
            public: $this->public,
            externalId: $this->externalId,
            owner: $this->owner,
            publicOwner: $this->publicOwner,
            tracks: $this->tracks,
            orderedTracks: $this->orderedTracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function public(bool $public = true): self
    {
        return new self(
            title: $this->title,
            public: $public,
            externalId: $this->externalId,
            owner: $this->owner,
            publicOwner: $this->publicOwner,
            tracks: $this->tracks,
            orderedTracks: $this->orderedTracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function externalId(?string $externalId): self
    {
        return new self(
            title: $this->title,
            public: $this->public,
            externalId: $externalId,
            owner: $this->owner,
            publicOwner: $this->publicOwner,
            tracks: $this->tracks,
            orderedTracks: $this->orderedTracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function owner(?Identifier $owner): self
    {
        return new self(
            title: $this->title,
            public: $this->public,
            externalId: $this->externalId,
            owner: $owner,
            publicOwner: $this->publicOwner,
            tracks: $this->tracks,
            orderedTracks: $this->orderedTracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function publicOwner(Identifier|PublicProfile|null $publicOwner): self
    {
        return new self(
            title: $this->title,
            public: $this->public,
            externalId: $this->externalId,
            owner: $this->owner,
            publicOwner: $publicOwner,
            tracks: $this->tracks,
            orderedTracks: $this->orderedTracks,
        );
    }

    /**
     * @param list<Identifier|Track> $tracks
     *
     * @phpstan-pure
     */
    public function tracks(array $tracks): self
    {
        return new self(
            title: $this->title,
            public: $this->public,
            externalId: $this->externalId,
            owner: $this->owner,
            publicOwner: $this->publicOwner,
            tracks: $tracks,
            orderedTracks: $this->orderedTracks,
        );
    }

    /**
     * @param list<Identifier|Track> $orderedTracks
     *
     * @phpstan-pure
     */
    public function orderedTracks(array $orderedTracks): self
    {
        return new self(
            title: $this->title,
            public: $this->public,
            externalId: $this->externalId,
            owner: $this->owner,
            publicOwner: $this->publicOwner,
            tracks: $this->tracks,
            orderedTracks: $orderedTracks,
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
     * @param PlaylistUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `playlists`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Playlist::WRITABLE, true)) {
                throw UnknownAttributeException::for(PlaylistBase::TYPE, (string) $member, Playlist::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            title: \array_key_exists('title', $input) ? $input['title'] : Missing::Value,
            public: \array_key_exists('public', $input) ? $input['public'] : Missing::Value,
            externalId: \array_key_exists('externalId', $input) ? $input['externalId'] : Missing::Value,
            owner: \array_key_exists('owner', $input) ? $input['owner'] : Missing::Value,
            publicOwner: \array_key_exists('publicOwner', $input) ? $input['publicOwner'] : Missing::Value,
            tracks: \array_key_exists('tracks', $input) ? $input['tracks'] : Missing::Value,
            orderedTracks: \array_key_exists('orderedTracks', $input) ? $input['orderedTracks'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /playlists/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => PlaylistBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->title instanceof Missing) {
            $attributes['title'] = $this->title;
        }

        if (!$this->public instanceof Missing) {
            $attributes['public'] = $this->public;
        }

        if (!$this->externalId instanceof Missing) {
            $attributes['externalId'] = $this->externalId;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->owner instanceof Missing) {
            $relationships['owner'] = ['data' => Coerce::linkage($this->owner)];
        }

        if (!$this->publicOwner instanceof Missing) {
            $relationships['publicOwner'] = ['data' => Coerce::linkage($this->publicOwner)];
        }

        if (!$this->tracks instanceof Missing) {
            $relationships['tracks'] = ['data' => Coerce::linkageList($this->tracks)];
        }

        if (!$this->orderedTracks instanceof Missing) {
            $relationships['orderedTracks'] = ['data' => Coerce::linkageList($this->orderedTracks)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}
