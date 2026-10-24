<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
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

    /** @var non-empty-string */
    public string $title { get => $this->title(); }

    public ?string $slug { get => $this->slug(); }

    public ?bool $public { get => $this->public(); }

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
     * @return non-empty-string
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `title`
     */
    public function title(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('title');

        return $value;
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * Matches `^[a-z0-9]+(?:-[a-z0-9]+)*$`.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `slug`
     */
    public function slug(): ?string
    {
        return $this->_nullableString('slug');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `public`
     */
    public function public(): ?bool
    {
        return $this->_nullableBool('public');
    }

    /**
     * In `uuid` format.
     *
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
 * @phpstan-type PlaylistCreateShape array{title: non-empty-string, public?: bool, externalId?: string|null, owner?: PlaylistOwnerInput, publicOwner?: PlaylistPublicOwnerInput, tracks?: PlaylistTracksInput, orderedTracks?: PlaylistOrderedTracksInput}
 * @phpstan-type PlaylistUpdateShape array{title?: non-empty-string, public?: bool, externalId?: string|null, owner?: PlaylistOwnerInput, publicOwner?: PlaylistPublicOwnerInput, tracks?: PlaylistTracksInput, orderedTracks?: PlaylistOrderedTracksInput}
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
     * @param non-empty-string $title
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

    /**
     * @var non-empty-string|Missing
     */
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

    /**
     * @param non-empty-string $title
     */
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

    /**
     * In `uuid` format.
     */
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
 * One playlist by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type PlaylistUpdateShape from Playlist
 *
 * @generated from the `playlists` operations on one resource
 */
final class PlaylistHandle
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
     * Fetch the playlist, shaped by the optional projection: a closure over a fresh projection, or
     * a prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(PlaylistProjection<PlaylistBase>): PlaylistProjection<TProj>)|PlaylistProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PlaylistBase : PlaylistBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|PlaylistProjection|null $projection = null): PlaylistBase
    {
        $query = PlaylistProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/playlists/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The playlists response', 'data');

        /** @var PlaylistBase&TProj */
        return $context->hydrate($raw, Playlist::_hydrate(...));
    }

    /**
     * Patch the playlist, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param PlaylistUpdate|PlaylistUpdateShape                                                                     $input
     * @param (callable(PlaylistProjection<PlaylistBase>): PlaylistProjection<TProj>)|PlaylistProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PlaylistBase : PlaylistBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function update(
        PlaylistUpdate|array $input,
        callable|PlaylistProjection|null $projection = null,
    ): PlaylistBase {
        $document = match (true) {
            $input instanceof PlaylistUpdate => $input,
            default => PlaylistUpdate::from($input),
        };

        $query = PlaylistProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/playlists/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var PlaylistBase&TProj */
        return $context->hydrate($raw, Playlist::_hydrate(...));
    }

    /**
     * Delete the playlist.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/playlists/' . $this->id));
    }

    /**
     * The `orderedTracks` relationship, a to-many whose endpoint advertises `add()`, `remove()` and
     * `replace()`.
     */
    public function orderedTracks(): PlaylistOrderedTracksRelation
    {
        return new PlaylistOrderedTracksRelation($this->fetcher, $this->id);
    }

    /**
     * The `owner` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function owner(): PlaylistOwnerRelation
    {
        return new PlaylistOwnerRelation($this->fetcher, $this->id);
    }

    /**
     * The `publicOwner` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function publicOwner(): PlaylistPublicOwnerRelation
    {
        return new PlaylistPublicOwnerRelation($this->fetcher, $this->id);
    }

    /**
     * The `tracks` relationship, a to-many whose endpoint advertises `add()`, `remove()` and
     * `replace()`.
     */
    public function tracks(): PlaylistTracksRelation
    {
        return new PlaylistTracksRelation($this->fetcher, $this->id);
    }
}

/**
 * One `orderedTracks` member and its pivot data, for a relationship write. Only the writable pivot
 * fields are here (`addedAt` is read-only), and a required one takes no default, so leaving it out
 * fails before the request exists.
 *
 * @generated from the `meta.pivot` of `playlists.orderedTracks` linkage
 */
final class PlaylistOrderedTracksEdge
{
    public function __construct(
        public readonly Identifier|Track $target,
        public readonly int $position,
        public readonly int|Missing $weight = Missing::Value,
    ) {}

    /**
     * The linkage member this edge writes, its pivot under `meta.pivot`.
     *
     * @return array{type: string, id: string, meta: array{pivot: array<string, mixed>}}
     */
    public function toLinkage(): array
    {
        $pivot = [
            'position' => $this->position,
        ];

        if (!$this->weight instanceof Missing) {
            $pivot['weight'] = $this->weight;
        }

        $identifier = $this->target instanceof Identifier ? $this->target : $this->target->_identifier();

        return ['type' => $identifier->type, 'id' => $identifier->id, 'meta' => ['pivot' => $pivot]];
    }
}

/**
 * The `orderedTracks` relationship of one playlist. `get()` reads the linkage and `related()` reads
 * the related resources; any read the server switched off is absent. The writes are `add()`,
 * `remove()` and `replace()`, the methods the relationship endpoint declares, and no others.
 * Members are written as {@see PlaylistOrderedTracksEdge}, which carries the writable pivot fields.
 *
 * @generated from `/playlists/{id}/orderedTracks` and `/playlists/{id}/relationships/orderedTracks`
 */
final class PlaylistOrderedTracksRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone, a page at a time, without fetching the related resources.
     *
     * @return PaginatedCollection<Identifier, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/playlists/' . $this->id . '/relationships/orderedTracks')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Track, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/playlists/' . $this->id . '/orderedTracks')));
    }

    /**
     * Add members to the relationship, leaving the rest alone.
     *
     * @param list<PlaylistOrderedTracksEdge> $orderedTracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function add(array $orderedTracks): void
    {
        $this->fetcher->send(Request::post(
            '/playlists/' . $this->id . '/relationships/orderedTracks',
            \json_encode(['data' => self::edges($orderedTracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove members from the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Track> $orderedTracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function remove(array $orderedTracks): void
    {
        $this->fetcher->send(Request::delete(
            '/playlists/' . $this->id . '/relationships/orderedTracks',
            \json_encode(['data' => Coerce::linkageList($orderedTracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Replace every member of the relationship.
     *
     * @param list<PlaylistOrderedTracksEdge> $orderedTracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function replace(array $orderedTracks): void
    {
        $this->fetcher->send(Request::patch(
            '/playlists/' . $this->id . '/relationships/orderedTracks',
            \json_encode(['data' => self::edges($orderedTracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * @return PaginatedCollection<Identifier, PageNumber>
     */
    private function linkage(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $identifiers = [];

        foreach ($context->primaryList() as $raw) {
            $identifiers[] = Identifier::fromArray($raw, 'the "orderedTracks" linkage');
        }

        return new PaginatedCollection(
            $identifiers,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->linkage($this->fetcher->readUrl($url)),
        );
    }

    /**
     * @return PaginatedCollection<Track, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $orderedTracks = [];

        foreach ($context->primaryList() as $raw) {
            $orderedTracks[] = Track::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $orderedTracks,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }

    /**
     * @param list<PlaylistOrderedTracksEdge> $edges
     *
     * @return list<array{type: string, id: string, meta: array{pivot: array<string, mixed>}}>
     */
    private static function edges(array $edges): array
    {
        return \array_map(static fn(PlaylistOrderedTracksEdge $edge): array => $edge->toLinkage(), $edges);
    }
}

/**
 * The `owner` relationship of one playlist. `get()` reads the linkage; any read the server switched
 * off is absent. The writes are `set()`, the methods the relationship endpoint declares, and no
 * others.
 *
 * @generated from `/playlists/{id}/relationships/owner`
 */
final class PlaylistOwnerRelation
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
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/playlists/' . $this->id . '/relationships/owner'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "owner" linkage');
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function set(Identifier|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/playlists/' . $this->id . '/relationships/owner',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
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
 * The `publicOwner` relationship of one playlist. `get()` reads the linkage and `related()` reads
 * the related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/playlists/{id}/publicOwner` and `/playlists/{id}/relationships/publicOwner`
 */
final class PlaylistPublicOwnerRelation
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
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/playlists/' . $this->id . '/relationships/publicOwner'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "publicOwner" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): ?PublicProfile
    {
        $context = $this->fetcher->read(Request::get('/playlists/' . $this->id . '/publicOwner'));
        $raw = $context->primary();

        return $raw === null ? null : PublicProfile::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function set(Identifier|PublicProfile|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/playlists/' . $this->id . '/relationships/publicOwner',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * A read of `GET /playlists`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
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
     * Paginator parameters assembled at runtime. Public here because `GET /playlists` is paginated;
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
 * The `tracks` relationship of one playlist. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `add()`, `remove()`
 * and `replace()`, the methods the relationship endpoint declares, and no others.
 *
 * @generated from `/playlists/{id}/tracks` and `/playlists/{id}/relationships/tracks`
 */
final class PlaylistTracksRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone, a page at a time, without fetching the related resources.
     *
     * @return PaginatedCollection<Identifier, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/playlists/' . $this->id . '/relationships/tracks')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Track, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no playlist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/playlists/' . $this->id . '/tracks')));
    }

    /**
     * Add members to the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Track> $tracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function add(array $tracks): void
    {
        $this->fetcher->send(Request::post(
            '/playlists/' . $this->id . '/relationships/tracks',
            \json_encode(['data' => Coerce::linkageList($tracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove members from the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Track> $tracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function remove(array $tracks): void
    {
        $this->fetcher->send(Request::delete(
            '/playlists/' . $this->id . '/relationships/tracks',
            \json_encode(['data' => Coerce::linkageList($tracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Replace every member of the relationship.
     *
     * @param list<Identifier|Track> $tracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no playlist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function replace(array $tracks): void
    {
        $this->fetcher->send(Request::patch(
            '/playlists/' . $this->id . '/relationships/tracks',
            \json_encode(['data' => Coerce::linkageList($tracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * @return PaginatedCollection<Identifier, PageNumber>
     */
    private function linkage(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $identifiers = [];

        foreach ($context->primaryList() as $raw) {
            $identifiers[] = Identifier::fromArray($raw, 'the "tracks" linkage');
        }

        return new PaginatedCollection(
            $identifiers,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->linkage($this->fetcher->readUrl($url)),
        );
    }

    /**
     * @return PaginatedCollection<Track, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $tracks = [];

        foreach ($context->primaryList() as $raw) {
            $tracks[] = Track::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $tracks,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
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
     * @param non-empty-string|Missing $title
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
     * @param non-empty-string $title
     *
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
     * In `uuid` format.
     *
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

/**
 * The `playlists` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type PlaylistCreateShape from Playlist
 *
 * @generated from the `playlists` operations
 */
final class Playlists
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the playlists collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return PlaylistQuery<PlaylistBase>
     */
    public function query(): PlaylistQuery
    {
        /** @var PlaylistQuery<PlaylistBase> */
        return new PlaylistQuery($this->fetcher);
    }

    /**
     * A handle on one playlist by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): PlaylistHandle
    {
        return new PlaylistHandle($this->fetcher, $id);
    }

    /**
     * Create a playlist, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param PlaylistCreate|PlaylistCreateBuilder|PlaylistCreateShape                                               $input
     * @param (callable(PlaylistProjection<PlaylistBase>): PlaylistProjection<TProj>)|PlaylistProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PlaylistBase : PlaylistBase&TProj)
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
     */
    public function create(
        PlaylistCreate|PlaylistCreateBuilder|array $input,
        callable|PlaylistProjection|null $projection = null,
    ): PlaylistBase {
        $document = match (true) {
            $input instanceof PlaylistCreate => $input,
            $input instanceof PlaylistCreateBuilder => $input->build(),
            default => PlaylistCreate::from($input),
        };

        $query = PlaylistProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/playlists'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var PlaylistBase&TProj */
        return $context->hydrate($raw, Playlist::_hydrate(...));
    }
}
