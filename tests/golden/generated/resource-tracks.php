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
use haddowg\JsonApiClient\Exceptions\UnknownFilterException;
use haddowg\JsonApiClient\Exceptions\UnknownSortException;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\Cursor;
use haddowg\JsonApiClient\Pagination\Offset;
use haddowg\JsonApiClient\Pagination\Page;
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
 * The projection surface of `tracks`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see TrackProjection}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `tracks` read operations
 */
trait TrackProjecting
{
    /**
     * Include the `album` relation, narrowing the projection so `$track->album` type-checks.
     *
     * @return static<TProj&TrackHasAlbum>
     */
    public function withAlbum(): static
    {
        return $this->including('album');
    }

    /**
     * Include the `playlists` relation, narrowing the projection so `$track->playlists`
     * type-checks.
     *
     * @return static<TProj&TrackHasPlaylists>
     */
    public function withPlaylists(): static
    {
        return $this->including('playlists');
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
     * Sparse fieldsets, with the `tracks` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[tracks]` names a member `tracks` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[TrackBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Track::FIELDS, true)) {
                throw UnknownFieldException::for(TrackBase::TYPE, $field, Track::FIELDS);
            }
        }

        return parent::fields($fields);
    }
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
 * @phpstan-type TrackAlbumInput Album|Identifier|null
 * @phpstan-type TrackPlaylistsInput list<Identifier|Playlist>
 * @phpstan-type TrackCreateShape array{title: string, trackNumber?: int, durationSeconds?: int, explicit?: bool, genres?: list<string>, previewOffset?: string|null, album?: TrackAlbumInput, playlists?: TrackPlaylistsInput}
 * @phpstan-type TrackUpdateShape array{title?: string, trackNumber?: int, durationSeconds?: int, explicit?: bool, genres?: list<string>, previewOffset?: string|null, album?: TrackAlbumInput}
 * @phpstan-type TrackFilterShape array{title?: string, explicit?: bool, genres?: list<string>, q?: string}
 * @phpstan-type TrackSortToken '-title'|'-trackNumber'|'title'|'trackNumber'
 * @phpstan-type TrackIncludePath 'album'|'album.artist'|'album.tracks'|'playlists'|'playlists.orderedTracks'|'playlists.publicOwner'|'playlists.tracks'
 * @phpstan-type TrackFieldToken 'album'|'displayTitle'|'durationSeconds'|'explicit'|'genres'|'playlists'|'previewOffset'|'title'|'trackNumber'
 * @phpstan-type TrackCountToken 'playlists'
 *
 * @generated from `components.schemas.TracksResource`
 */
final class Track extends TrackBase implements TrackHasAlbum, TrackHasPlaylists
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = [
        'title',
        'trackNumber',
        'durationSeconds',
        'explicit',
        'genres',
        'previewOffset',
        'album',
        'playlists',
    ];

    /**
     * The members the update document accepts, where they differ from `WRITABLE`.
     *
     * @var list<string>
     */
    public const array WRITABLE_ON_UPDATE = [
        'title',
        'trackNumber',
        'durationSeconds',
        'explicit',
        'genres',
        'previewOffset',
        'album',
    ];

    /**
     * Members a create document requires. Read-only attributes (`displayTitle`) are in no write
     * shape.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['title'];

    /**
     * The filters `GET /tracks` accepts. The runtime guard on both array doors checks names against
     * this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array FILTERS = ['title', 'explicit', 'genres', 'q'];

    /**
     * The signed sort tokens `GET /tracks` accepts, both directions of each.
     *
     * @var list<string>
     */
    public const array SORT_TOKENS = ['title', '-title', 'trackNumber', '-trackNumber'];

    /**
     * The members `fields[tracks]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = [
        'title',
        'trackNumber',
        'durationSeconds',
        'explicit',
        'genres',
        'previewOffset',
        'displayTitle',
        'album',
        'playlists',
    ];

    /**
     * The `withCount` tokens `GET /tracks` advertises.
     *
     * @var list<string>
     */
    public const array COUNT_TOKENS = ['playlists'];

    /**
     * The Countable profile, which the client adds to `Accept` only when a request uses
     * `withCount`.
     */
    public const string COUNTABLE_PROFILE = 'https://haddowg.github.io/json-api/profiles/countable/';

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

/**
 * The `album` relationship of one track. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/tracks/{id}/album` and `/tracks/{id}/relationships/album`
 */
final class TrackAlbumRelation
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
     * @throws NotFound           when no track has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/tracks/' . $this->id . '/relationships/album'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "album" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no track has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): ?Album
    {
        $context = $this->fetcher->read(Request::get('/tracks/' . $this->id . '/album'));
        $raw = $context->primary();

        return $raw === null ? null : Album::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no track has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function set(Album|Identifier|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/tracks/' . $this->id . '/relationships/album',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * The canonical create input for `tracks`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type TrackCreateShape from Track
 *
 * @generated from `POST /tracks`
 */
final class TrackCreate
{
    /**
     * @param list<string>|Missing $genres
     * @param Album|Identifier|Missing|null $album
     * @param list<Identifier|Playlist>|Missing $playlists
     */
    public function __construct(
        public readonly string $title,
        public readonly int|Missing $trackNumber = Missing::Value,
        public readonly int|Missing $durationSeconds = Missing::Value,
        public readonly bool|Missing $explicit = Missing::Value,
        public readonly array|Missing $genres = Missing::Value,
        public readonly string|Missing|null $previewOffset = Missing::Value,
        public readonly Album|Identifier|Missing|null $album = Missing::Value,
        public readonly array|Missing $playlists = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param TrackCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `tracks`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Track::WRITABLE, true)) {
                throw UnknownAttributeException::for(TrackBase::TYPE, (string) $member, Track::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            title: $input['title'],
            trackNumber: \array_key_exists('trackNumber', $input) ? $input['trackNumber'] : Missing::Value,
            durationSeconds: \array_key_exists('durationSeconds', $input) ? $input['durationSeconds'] : Missing::Value,
            explicit: \array_key_exists('explicit', $input) ? $input['explicit'] : Missing::Value,
            genres: \array_key_exists('genres', $input) ? $input['genres'] : Missing::Value,
            previewOffset: \array_key_exists('previewOffset', $input) ? $input['previewOffset'] : Missing::Value,
            album: \array_key_exists('album', $input) ? $input['album'] : Missing::Value,
            playlists: \array_key_exists('playlists', $input) ? $input['playlists'] : Missing::Value,
        );
    }

    /**
     * The `POST /tracks` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => TrackBase::TYPE];

        $attributes = ['title' => $this->title];

        if (!$this->trackNumber instanceof Missing) {
            $attributes['trackNumber'] = $this->trackNumber;
        }

        if (!$this->durationSeconds instanceof Missing) {
            $attributes['durationSeconds'] = $this->durationSeconds;
        }

        if (!$this->explicit instanceof Missing) {
            $attributes['explicit'] = $this->explicit;
        }

        if (!$this->genres instanceof Missing) {
            $attributes['genres'] = $this->genres;
        }

        if (!$this->previewOffset instanceof Missing) {
            $attributes['previewOffset'] = $this->previewOffset;
        }

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->album instanceof Missing) {
            $relationships['album'] = ['data' => Coerce::linkage($this->album)];
        }

        if (!$this->playlists instanceof Missing) {
            $relationships['playlists'] = ['data' => Coerce::linkageList($this->playlists)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `tracks`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /tracks`
 */
final class TrackCreateBuilder
{
    use Conditionable;

    private string|Missing $title = Missing::Value;

    private int|Missing $trackNumber = Missing::Value;

    private int|Missing $durationSeconds = Missing::Value;

    private bool|Missing $explicit = Missing::Value;

    /**
     * @var list<string>|Missing
     */
    private array|Missing $genres = Missing::Value;

    private string|Missing|null $previewOffset = Missing::Value;

    private Album|Identifier|Missing|null $album = Missing::Value;

    /**
     * @var list<Identifier|Playlist>|Missing
     */
    private array|Missing $playlists = Missing::Value;

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function trackNumber(int $trackNumber): self
    {
        $this->trackNumber = $trackNumber;

        return $this;
    }

    public function durationSeconds(int $durationSeconds): self
    {
        $this->durationSeconds = $durationSeconds;

        return $this;
    }

    public function explicit(bool $explicit = true): self
    {
        $this->explicit = $explicit;

        return $this;
    }

    /**
     * @param list<string> $genres
     */
    public function genres(array $genres): self
    {
        $this->genres = $genres;

        return $this;
    }

    public function previewOffset(?string $previewOffset): self
    {
        $this->previewOffset = $previewOffset;

        return $this;
    }

    public function album(Album|Identifier|null $album): self
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @param list<Identifier|Playlist> $playlists
     */
    public function playlists(array $playlists): self
    {
        $this->playlists = $playlists;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): TrackCreate
    {
        if ($this->title instanceof Missing) {
            throw MissingRequiredMemberException::for(TrackBase::TYPE, ['title']);
        }

        return new TrackCreate(
            title: $this->title,
            trackNumber: $this->trackNumber,
            durationSeconds: $this->durationSeconds,
            explicit: $this->explicit,
            genres: $this->genres,
            previewOffset: $this->previewOffset,
            album: $this->album,
            playlists: $this->playlists,
        );
    }
}

/**
 * One track by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type TrackUpdateShape from Track
 *
 * @generated from the `tracks` operations on one resource
 */
final class TrackHandle
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
     * Fetch the track, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(TrackProjection<TrackBase>): TrackProjection<TProj>)|TrackProjection<TProj>|null $projection
     *
     * @return ($projection is null ? TrackBase : TrackBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no track has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|TrackProjection|null $projection = null): TrackBase
    {
        $query = TrackProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/tracks/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The tracks response', 'data');

        /** @var TrackBase&TProj */
        return $context->hydrate($raw, Track::_hydrate(...));
    }

    /**
     * Patch the track, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param TrackUpdate|TrackUpdateShape                                                               $input
     * @param (callable(TrackProjection<TrackBase>): TrackProjection<TProj>)|TrackProjection<TProj>|null $projection
     *
     * @return ($projection is null ? TrackBase : TrackBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no track has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function update(
        TrackUpdate|array $input,
        callable|TrackProjection|null $projection = null,
    ): TrackBase {
        $document = match (true) {
            $input instanceof TrackUpdate => $input,
            default => TrackUpdate::from($input),
        };

        $query = TrackProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/tracks/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var TrackBase&TProj */
        return $context->hydrate($raw, Track::_hydrate(...));
    }

    /**
     * Delete the track.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no track has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/tracks/' . $this->id));
    }

    /**
     * The `album` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function album(): TrackAlbumRelation
    {
        return new TrackAlbumRelation($this->fetcher, $this->id);
    }

    /**
     * The `playlists` relationship, a to-many whose endpoint advertises `add()` and `remove()`.
     */
    public function playlists(): TrackPlaylistsRelation
    {
        return new TrackPlaylistsRelation($this->fetcher, $this->id);
    }
}

/**
 * The `playlists` relationship of one track. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `add()` and
 * `remove()`, the methods the relationship endpoint declares, and no others.
 *
 * @generated from `/tracks/{id}/playlists` and `/tracks/{id}/relationships/playlists`
 */
final class TrackPlaylistsRelation
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
     * @throws NotFound           when no track has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/tracks/' . $this->id . '/relationships/playlists')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Playlist, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no track has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/tracks/' . $this->id . '/playlists')));
    }

    /**
     * Add members to the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Playlist> $playlists
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no track has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function add(array $playlists): void
    {
        $this->fetcher->send(Request::post(
            '/tracks/' . $this->id . '/relationships/playlists',
            \json_encode(['data' => Coerce::linkageList($playlists)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove members from the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Playlist> $playlists
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no track has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function remove(array $playlists): void
    {
        $this->fetcher->send(Request::delete(
            '/tracks/' . $this->id . '/relationships/playlists',
            \json_encode(['data' => Coerce::linkageList($playlists)], \JSON_THROW_ON_ERROR),
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
            $identifiers[] = Identifier::fromArray($raw, 'the "playlists" linkage');
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
     * @return PaginatedCollection<Playlist, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $playlists = [];

        foreach ($context->primaryList() as $raw) {
            $playlists[] = Playlist::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $playlists,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }
}

/**
 * What a response carrying `tracks` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /tracks/{id}`, `POST /tracks`, `PATCH /tracks/{id}`
 */
final class TrackProjection extends Projection
{
    /** @use TrackProjecting<TProj> */
    use TrackProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<TrackBase>
     */
    public static function make(): self
    {
        /** @var self<TrackBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<TrackBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /tracks`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not. Filters come in three doors:
 * a `whereX()` per filter, the typed `filter()` shape, and the loose `filterRaw()`. They are
 * separate methods because a parameter typed as the shape or any array would accept anything. Both
 * array doors still check names at runtime.
 *
 * The endpoint offers a menu of pagination strategies. `TPage` is the page a read returns: the
 * server's default, `page`, until a `pageByX()` picker chooses another.
 *
 * @template TProj of object
 *
 * @template TPage of Page
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type TrackCountToken from Track
 * @phpstan-import-type TrackFilterShape from Track
 * @phpstan-import-type TrackIncludePath from Track
 * @phpstan-import-type TrackSortToken from Track
 *
 * @generated from `GET /tracks`
 */
final class TrackQuery extends Query
{
    use QueryConditionable;

    /**
     * @param \Closure(array<string, mixed>, PageLinks): TPage $reader
     */
    public function __construct(
        private readonly Fetcher $fetcher,
        private \Closure $reader,
    ) {}

    /**
     * Include the `album` relation, narrowing the projection so `$track->album` type-checks.
     *
     * @return static<TProj&TrackHasAlbum, TPage>
     */
    public function withAlbum(): static
    {
        return $this->including('album');
    }

    /**
     * Include the `playlists` relation, narrowing the projection so `$track->playlists`
     * type-checks.
     *
     * @return static<TProj&TrackHasPlaylists, TPage>
     */
    public function withPlaylists(): static
    {
        return $this->including('playlists');
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
     * Sparse fieldsets, with the `tracks` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[tracks]` names a member `tracks` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[TrackBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Track::FIELDS, true)) {
                throw UnknownFieldException::for(TrackBase::TYPE, $field, Track::FIELDS);
            }
        }

        return parent::fields($fields);
    }

    /**
     * Filter by `title`.
     *
     * @return static
     */
    public function whereTitle(string $value): static
    {
        return $this->filtering(['title' => $value]);
    }

    /**
     * Filter by `explicit`.
     *
     * @return static
     */
    public function whereExplicit(bool $value): static
    {
        return $this->filtering(['explicit' => $value]);
    }

    /**
     * Filter by `genres`.
     *
     * @param list<string> $value
     *
     * @return static
     */
    public function whereGenres(array $value): static
    {
        return $this->filtering(['genres' => $value]);
    }

    /**
     * Filter by `q`.
     *
     * @return static
     */
    public function whereQ(string $value): static
    {
        return $this->filtering(['q' => $value]);
    }

    /**
     * The typed array door: values are checked statically, nested ones included, and names at
     * runtime.
     *
     * @param TrackFilterShape $filter
     *
     * @return static
     *
     * @throws UnknownFilterException when a name is not one this endpoint filters by
     */
    public function filter(array $filter): static
    {
        return $this->filtering(self::guardFilters($filter));
    }

    /**
     * The loose door, for a filter map assembled at runtime. Nothing is checked statically; the
     * names are still checked here.
     *
     * @param array<string, mixed> $filter
     *
     * @return static
     *
     * @throws UnknownFilterException when a name is not one this endpoint filters by
     */
    public function filterRaw(array $filter): static
    {
        return $this->filtering(self::guardFilters($filter));
    }

    /**
     * Sort by the endpoint's own tokens, in precedence order. Argument order is precedence, so
     * nothing is translated between the call and the wire.
     *
     * @param TrackSortToken ...$tokens
     *
     * @return static
     */
    public function sort(string ...$tokens): static
    {
        return $this->sorting(\array_values($tokens));
    }

    /**
     * The loose sort door, for tokens assembled at runtime, which a literal union can never accept.
     *
     * @param list<string> $tokens
     *
     * @return static
     *
     * @throws UnknownSortException when a token is not one this endpoint sorts by
     */
    public function sortRaw(array $tokens): static
    {
        foreach ($tokens as $token) {
            if (!\in_array($token, Track::SORT_TOKENS, true)) {
                throw UnknownSortException::for($token, Track::SORT_TOKENS);
            }
        }

        return $this->sorting($tokens);
    }

    /**
     * Count related resources without including them, under the Countable profile. `GET /tracks`
     * advertises `playlists`. A counted relation is still not an included one: its count is read
     * through `_rel()`.
     *
     * @param list<TrackCountToken> $tokens
     *
     * @return static
     */
    public function withCount(array $tokens): static
    {
        return $this->counting($tokens);
    }

    /**
     * Page by the `page` strategy, the one the server uses when a read picks none. Sends
     * `page[kind]=page` with whichever members are given, replacing whatever an earlier picker set,
     * and reads `PageNumber` pages.
     *
     * @param positive-int|null $number
     * @param positive-int|null $size
     *
     * @return self<TProj, PageNumber>
     */
    public function pageByPage(?int $number = null, ?int $size = null): self
    {
        return $this->choose(['kind' => 'page', 'number' => $number, 'size' => $size], PageNumber::read(...));
    }

    /**
     * Page by the `cursor` strategy. Sends `page[kind]=cursor` with whichever members are given,
     * replacing whatever an earlier picker set, and reads `Cursor` pages.
     *
     * @param string|null       $after
     * @param string|null       $before
     * @param positive-int|null $size
     *
     * @return self<TProj, Cursor>
     */
    public function pageByCursor(?string $after = null, ?string $before = null, ?int $size = null): self
    {
        return $this->choose(['kind' => 'cursor', 'after' => $after, 'before' => $before, 'size' => $size], Cursor::read(...));
    }

    /**
     * Page by the `offset` strategy. Sends `page[kind]=offset` with whichever members are given,
     * replacing whatever an earlier picker set, and reads `Offset` pages.
     *
     * @param int<0, max>|null  $offset
     * @param positive-int|null $limit
     *
     * @return self<TProj, Offset>
     */
    public function pageByOffset(?int $offset = null, ?int $limit = null): self
    {
        return $this->choose(['kind' => 'offset', 'offset' => $offset, 'limit' => $limit], Offset::read(...));
    }

    /**
     * Choose a strategy: replace the page parameters with its own, dropping those left null, and
     * re-type the query to read its page. The runtime hands the replaced query back as the base
     * `Query`, which is what makes narrowing it to the new page type legal.
     *
     * @template TNext of Page
     *
     * @param array<string, mixed>                              $page
     * @param \Closure(array<string, mixed>, PageLinks): TNext $reader
     *
     * @return self<TProj, TNext>
     */
    private function choose(array $page, \Closure $reader): self
    {
        /** @var self<TProj, TNext> $chosen */
        $chosen = $this->repaging(\array_filter($page, static fn(mixed $value): bool => $value !== null));
        $chosen->reader = $reader;

        return $chosen;
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /tracks` is paginated;
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
     * @return PaginatedCollection<TProj, TPage>
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
     * The first track the read matches.
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
        return $this->get()->first();
    }

    /**
     * @param TrackFilterShape|array<string, mixed> $filter
     *
     * @return array<string, mixed>
     *
     * @throws UnknownFilterException
     */
    private static function guardFilters(array $filter): array
    {
        foreach (\array_keys($filter) as $name) {
            $name = (string) $name;

            if (!\in_array($name, Track::FILTERS, true)) {
                throw UnknownFilterException::for($name, Track::FILTERS);
            }
        }

        return $filter;
    }

    /**
     * The Countable profile is negotiated only when a request actually uses a `withCount`
     * parameter, which is what `x-profile` on that parameter states.
     */
    private function request(ReadQuery $query): Request
    {
        $request = Request::get($query->appendTo('/tracks'));

        return $query->withCount === [] ? $request : $request->withProfiles([Track::COUNTABLE_PROFILE]);
    }

    /**
     * @return PaginatedCollection<TProj, TPage>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $tracks = [];

        foreach ($context->primaryList() as $raw) {
            $tracks[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $tracks,
            ($this->reader)($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Track`, but `TProj`
     * is a template parameter no bound can prove it satisfies. `ResourceContext::hydrate()` erases
     * the concrete class so the assertion is legal.
     *
     * @param array<string, mixed> $raw
     *
     * @return TProj
     */
    private function hydrate(array $raw, ResourceContext $context): object
    {
        /** @var TProj */
        return $context->hydrate($raw, Track::_hydrate(...));
    }
}

/**
 * The canonical update input for `tracks`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type TrackUpdateShape from Track
 *
 * @generated from `PATCH /tracks/{id}`
 */
final class TrackUpdate
{
    use ImmutableConditionable;

    /**
     * @param list<string>|Missing $genres
     * @param Album|Identifier|Missing|null $album
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $title = Missing::Value,
        public readonly int|Missing $trackNumber = Missing::Value,
        public readonly int|Missing $durationSeconds = Missing::Value,
        public readonly bool|Missing $explicit = Missing::Value,
        public readonly array|Missing $genres = Missing::Value,
        public readonly string|Missing|null $previewOffset = Missing::Value,
        public readonly Album|Identifier|Missing|null $album = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function title(string $title): self
    {
        return new self(
            title: $title,
            trackNumber: $this->trackNumber,
            durationSeconds: $this->durationSeconds,
            explicit: $this->explicit,
            genres: $this->genres,
            previewOffset: $this->previewOffset,
            album: $this->album,
        );
    }

    /**
     * @phpstan-pure
     */
    public function trackNumber(int $trackNumber): self
    {
        return new self(
            title: $this->title,
            trackNumber: $trackNumber,
            durationSeconds: $this->durationSeconds,
            explicit: $this->explicit,
            genres: $this->genres,
            previewOffset: $this->previewOffset,
            album: $this->album,
        );
    }

    /**
     * @phpstan-pure
     */
    public function durationSeconds(int $durationSeconds): self
    {
        return new self(
            title: $this->title,
            trackNumber: $this->trackNumber,
            durationSeconds: $durationSeconds,
            explicit: $this->explicit,
            genres: $this->genres,
            previewOffset: $this->previewOffset,
            album: $this->album,
        );
    }

    /**
     * @phpstan-pure
     */
    public function explicit(bool $explicit = true): self
    {
        return new self(
            title: $this->title,
            trackNumber: $this->trackNumber,
            durationSeconds: $this->durationSeconds,
            explicit: $explicit,
            genres: $this->genres,
            previewOffset: $this->previewOffset,
            album: $this->album,
        );
    }

    /**
     * @param list<string> $genres
     *
     * @phpstan-pure
     */
    public function genres(array $genres): self
    {
        return new self(
            title: $this->title,
            trackNumber: $this->trackNumber,
            durationSeconds: $this->durationSeconds,
            explicit: $this->explicit,
            genres: $genres,
            previewOffset: $this->previewOffset,
            album: $this->album,
        );
    }

    /**
     * @phpstan-pure
     */
    public function previewOffset(?string $previewOffset): self
    {
        return new self(
            title: $this->title,
            trackNumber: $this->trackNumber,
            durationSeconds: $this->durationSeconds,
            explicit: $this->explicit,
            genres: $this->genres,
            previewOffset: $previewOffset,
            album: $this->album,
        );
    }

    /**
     * @phpstan-pure
     */
    public function album(Album|Identifier|null $album): self
    {
        return new self(
            title: $this->title,
            trackNumber: $this->trackNumber,
            durationSeconds: $this->durationSeconds,
            explicit: $this->explicit,
            genres: $this->genres,
            previewOffset: $this->previewOffset,
            album: $album,
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
     * @param TrackUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `tracks`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Track::WRITABLE_ON_UPDATE, true)) {
                throw UnknownAttributeException::for(TrackBase::TYPE, (string) $member, Track::WRITABLE_ON_UPDATE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            title: \array_key_exists('title', $input) ? $input['title'] : Missing::Value,
            trackNumber: \array_key_exists('trackNumber', $input) ? $input['trackNumber'] : Missing::Value,
            durationSeconds: \array_key_exists('durationSeconds', $input) ? $input['durationSeconds'] : Missing::Value,
            explicit: \array_key_exists('explicit', $input) ? $input['explicit'] : Missing::Value,
            genres: \array_key_exists('genres', $input) ? $input['genres'] : Missing::Value,
            previewOffset: \array_key_exists('previewOffset', $input) ? $input['previewOffset'] : Missing::Value,
            album: \array_key_exists('album', $input) ? $input['album'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /tracks/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => TrackBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->title instanceof Missing) {
            $attributes['title'] = $this->title;
        }

        if (!$this->trackNumber instanceof Missing) {
            $attributes['trackNumber'] = $this->trackNumber;
        }

        if (!$this->durationSeconds instanceof Missing) {
            $attributes['durationSeconds'] = $this->durationSeconds;
        }

        if (!$this->explicit instanceof Missing) {
            $attributes['explicit'] = $this->explicit;
        }

        if (!$this->genres instanceof Missing) {
            $attributes['genres'] = $this->genres;
        }

        if (!$this->previewOffset instanceof Missing) {
            $attributes['previewOffset'] = $this->previewOffset;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->album instanceof Missing) {
            $relationships['album'] = ['data' => Coerce::linkage($this->album)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The `tracks` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type TrackCreateShape from Track
 *
 * @generated from the `tracks` operations
 */
final class Tracks
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the tracks collection, not yet sent: unprojected, unfiltered and unsorted. Until a
     * picker chooses a strategy, it reads the server's default, `page`.
     *
     * @return TrackQuery<TrackBase, PageNumber>
     */
    public function query(): TrackQuery
    {
        /** @var TrackQuery<TrackBase, PageNumber> */
        return new TrackQuery($this->fetcher, PageNumber::read(...));
    }

    /**
     * A handle on one track by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): TrackHandle
    {
        return new TrackHandle($this->fetcher, $id);
    }

    /**
     * Create a track, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param TrackCreate|TrackCreateBuilder|TrackCreateShape                                            $input
     * @param (callable(TrackProjection<TrackBase>): TrackProjection<TProj>)|TrackProjection<TProj>|null $projection
     *
     * @return ($projection is null ? TrackBase : TrackBase&TProj)
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
        TrackCreate|TrackCreateBuilder|array $input,
        callable|TrackProjection|null $projection = null,
    ): TrackBase {
        $document = match (true) {
            $input instanceof TrackCreate => $input,
            $input instanceof TrackCreateBuilder => $input->build(),
            default => TrackCreate::from($input),
        };

        $query = TrackProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/tracks'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var TrackBase&TProj */
        return $context->hydrate($raw, Track::_hydrate(...));
    }
}
