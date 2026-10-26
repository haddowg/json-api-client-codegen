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
use haddowg\JsonApiClient\Http\FilePart;
use haddowg\JsonApiClient\Http\FormEncoding;
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
use Psr\Http\Message\StreamInterface;

/**
 * The `artist` relation of an `albums` resource, hydrated.
 *
 * A marker carries no implementation. Its only job is to be intersected into a query builder's
 * projection by `withArtist()`, which is what makes `$album->artist` type-check after the
 * include and a compile error without it.
 *
 * The property hook is emitted only on an 8.4 target and mirrors the method exactly, so
 * `$album->artist` and `$album->artist()` are the same value read the same way.
 *
 * @generated from `components.schemas.AlbumsResource.properties.relationships.artist`
 */
interface AlbumHasArtist
{
    /**
     * The related `artists` resource, or null when the album has no artist.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `artist`
     */
    public function artist(): ?Artist;

    public ?Artist $artist { get; }
}

/**
 * The `tracks` relation of an `albums` resource, hydrated.
 *
 * A hydrated to-many value is a {@see ResourceCollection} and never a paginated one: the
 * resources arrived inside a compound document's `included`, which carries no page. Paging the
 * relation is what the related endpoint is for.
 *
 * @generated from `components.schemas.AlbumsResource.properties.relationships.tracks`
 */
interface AlbumHasTracks
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
 * The projection surface of `albums`: what a read brings back, shared by the write projection
 * and the query builder.
 *
 * A trait rather than a shared base class, and the reason is a level-9 result rather than
 * taste. `@return static<TProj&AlbumHasArtist>` over a `static`-returning primitive resolves
 * cleanly in a **final** class and is rejected in a non-final one:
 *
 * ```
 * Method …::withArtist() should return static(…<AlbumHasArtist&TProj of object>)
 * but returns static(…<TProj of object>).
 * ```
 *
 * So `AlbumQuery extends AlbumProjection` would put the narrowing methods on an extendable
 * class and fail. A trait used by two final classes is analysed once per using class, both of
 * them final, and passes for both — with no duplicated method bodies, no suppression
 * comment, and no inline `@var`.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `albums` read operations
 */
trait AlbumProjecting
{
    /**
     * Include the `artist` relation, narrowing the projection so `$album->artist` type-checks.
     *
     * @return static<TProj&AlbumHasArtist>
     */
    public function withArtist(): static
    {
        return $this->including('artist');
    }

    /**
     * Include the `tracks` relation, narrowing the projection so `$album->tracks` type-checks.
     *
     * @return static<TProj&AlbumHasTracks>
     */
    public function withTracks(): static
    {
        return $this->including('tracks');
    }

    /**
     * Include paths assembled at runtime, or deeper than one hop (`tracks.album`). Nothing is
     * narrowed, so a relation is reached through its always-safe companions or after a runtime
     * check. The runtime keeps this protected; it is public here because `albums` has relations
     * to include.
     *
     * @return static
     */
    public function with(string ...$paths): static
    {
        return parent::with(...$paths);
    }

    /**
     * Sparse fieldsets, with the primary type's tokens checked against the descriptor.
     *
     * Overriding the runtime method rather than adding a typed twin keeps one name for one
     * concept. The signature is unchanged — narrowing an inherited parameter to an array shape
     * would be a contravariance error — so the check is a runtime one, which is the right place
     * for it anyway: a fieldset assembled from a request could never satisfy a literal union.
     *
     * Only `fields[albums]` is checked here. A full run knows every type's members and checks
     * all of them.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[albums]` names a member `albums` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[AlbumBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Album::FIELDS, true)) {
                throw UnknownFieldException::for(AlbumBase::TYPE, $field, Album::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * An `albums` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * This is where a projection starts. `AlbumQuery<AlbumBase>` narrows to
 * `AlbumQuery<AlbumBase&AlbumHasArtist>` when `withArtist()` is called, so the relation
 * accessors are on the markers and deliberately not here — if they were, nothing would be
 * narrowed and `$album->tracks` would type-check on a read that never fetched it.
 *
 * Attributes *are* here, all of them, because a sparse fieldset is a wire concern. Reading one
 * the response did not carry throws rather than returning null: a null that means "excluded"
 * and a null that means "empty" are not the same answer, and only one of them is data.
 *
 * Values arrive coerced. `date-time`, `date` and `time` become `DateTimeImmutable`, `number`
 * becomes `float`, and the enumerated `status` becomes {@see AlbumStatus} — the same coercion
 * the server performs in the other direction, on the same wire form.
 *
 * @phpstan-import-type AlbumReleaseInfoShape from Album
 *
 * @generated from `components.schemas.AlbumsResource`
 */
abstract class AlbumBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'albums';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    /** @var non-empty-string */
    public string $title { get => $this->title(); }

    public ?float $averageRating { get => $this->averageRating(); }

    public ?string $artwork { get => $this->artwork(); }

    public ?\DateTimeImmutable $releasedAt { get => $this->releasedAt(); }

    public bool $explicit { get => $this->explicit(); }

    public AlbumStatus $status { get => $this->status(); }

    public ?\DateTimeImmutable $availableFrom { get => $this->availableFrom(); }

    public ?\DateTimeImmutable $availableUntil { get => $this->availableUntil(); }

    /** @var AlbumReleaseInfoShape|null */
    public ?array $releaseInfo { get => $this->releaseInfo(); }

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
     * At most 200 characters.
     *
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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `averageRating`
     */
    public function averageRating(): ?float
    {
        return $this->_nullableFloat('averageRating');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `artwork`
     */
    public function artwork(): ?string
    {
        return $this->_nullableString('artwork');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `releasedAt`
     */
    public function releasedAt(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('releasedAt');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `explicit`
     */
    public function explicit(): bool
    {
        return $this->_bool('explicit');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `status`
     */
    public function status(): AlbumStatus
    {
        return $this->_enum('status', AlbumStatus::class);
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `availableFrom`
     */
    public function availableFrom(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('availableFrom');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `availableUntil`
     */
    public function availableUntil(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('availableUntil');
    }

    /**
     * A nested object attribute, narrowed member by member.
     *
     * The runtime hands back `array<string, mixed>` because it knows nothing about this schema;
     * the declared shape is recovered here with a guard per member rather than an assertion,
     * so a server that sends the wrong type for `label` drops the member instead of poisoning
     * the type.
     *
     * @return AlbumReleaseInfoShape|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `releaseInfo`
     */
    public function releaseInfo(): ?array
    {
        $raw = $this->_nullableMap('releaseInfo');

        if ($raw === null) {
            return null;
        }

        $shape = [];
        $label = $raw['label'] ?? null;
        $catalogueNumber = $raw['catalogueNumber'] ?? null;

        if ($label === null && \array_key_exists('label', $raw)) {
            $shape['label'] = null;
        }

        if (\is_string($label)) {
            $shape['label'] = $label;
        }

        if ($catalogueNumber === null && \array_key_exists('catalogueNumber', $raw)) {
            $shape['catalogueNumber'] = null;
        }

        if (\is_string($catalogueNumber)) {
            $shape['catalogueNumber'] = $catalogueNumber;
        }

        return $shape;
    }

    /**
     * The `artist` linkage, without hydrating anything. Never throws.
     */
    public function artistRef(): ?Identifier
    {
        return $this->_relationship('artist', true)->identifier();
    }

    /**
     * Whether the album links to an artist at all. Never throws.
     */
    public function hasArtist(): bool
    {
        return !$this->_relationship('artist', true)->isEmpty();
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
     * Whether the album links to any track. Never throws.
     */
    public function hasTracks(): bool
    {
        return !$this->_relationship('tracks', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves
     * behind.
     *
     * This is the door for a relation carrying no value. `withCount=tracks` counts without
     * including, so `$album->tracks` still throws and the count is read here — one concept
     * covering introspection, links-only relations and counts alike.
     *
     * @param 'artist'|'tracks' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, $name === 'artist');
    }
}

/**
 * An `albums` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class — the projection is a *static* narrowing over it, so
 * `$album->tracks` exists at runtime whether or not it type-checks, and calling it without the
 * include throws. That asymmetry is the design: static checking where it is cheap, a loud
 * runtime backstop everywhere else.
 *
 * The write entry points are static factories here rather than on the client accessor. Both
 * arities return an inert value, so neither performs IO and a forgotten `create()` call cannot
 * silently no-op — and one import reaches every write form for the type.
 *
 * It also carries every array shape the `albums` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so they have to be
 * declared on some class and imported everywhere else they are used. That import is not
 * optional bookkeeping: without a matching `@phpstan-import-type` the alias silently degrades to
 * bare `array`, nothing is reported, and every array-shape check on that door is lost.
 *
 * @phpstan-type AlbumReleaseInfoShape array{label?: string|null, catalogueNumber?: string|null}
 * @phpstan-type AlbumStatusInput AlbumStatus|'released'|'upcoming'|'withdrawn'
 * @phpstan-type AlbumDateInput \DateTimeImmutable|string
 * @phpstan-type AlbumArtistInput Artist|Identifier|null
 * @phpstan-type AlbumTracksInput list<Identifier|Track>
 * @phpstan-type AlbumCreateShape array{title: non-empty-string, releasedAt?: AlbumDateInput, explicit?: bool, status?: AlbumStatusInput, availableFrom?: AlbumDateInput|null, availableUntil?: AlbumDateInput|null, releaseInfo?: array{label?: string, catalogueNumber?: string}|null, artist?: AlbumArtistInput, tracks?: AlbumTracksInput}
 * @phpstan-type AlbumUpdateShape array{title?: non-empty-string, releasedAt?: AlbumDateInput, explicit?: bool, status?: AlbumStatusInput, availableFrom?: AlbumDateInput|null, availableUntil?: AlbumDateInput|null, releaseInfo?: array{label?: string, catalogueNumber?: string}|null, artist?: AlbumArtistInput, tracks?: AlbumTracksInput}
 * @phpstan-type AlbumRangeShape array{min?: float, max?: float}
 * @phpstan-type AlbumDateRangeShape array{min?: AlbumDateInput, max?: AlbumDateInput}
 * @phpstan-type AlbumFilterShape array{tracks?: string, 'artist.name'?: string, title?: string, rating?: AlbumRangeShape, releasedAt?: AlbumDateRangeShape, q?: string}
 * @phpstan-type AlbumSortToken '-releasedAt'|'-status'|'-title'|'releasedAt'|'status'|'title'
 * @phpstan-type AlbumIncludePath 'artist'|'artist.albums'|'tracks'|'tracks.album'|'tracks.playlists'
 * @phpstan-type AlbumFieldToken 'artist'|'artwork'|'availableFrom'|'availableUntil'|'averageRating'|'explicit'|'releaseInfo'|'releasedAt'|'status'|'title'|'tracks'
 * @phpstan-type AlbumCountToken 'tracks'
 *
 * @generated from `components.schemas.AlbumsResource`
 */
final class Album extends AlbumBase implements AlbumHasArtist, AlbumHasTracks
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array
     * door checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = [
        'title',
        'releasedAt',
        'explicit',
        'status',
        'availableFrom',
        'availableUntil',
        'releaseInfo',
        'artist',
        'tracks',
    ];

    /**
     * Members a create document requires. `averageRating` and `artwork` are absent from
     * `AlbumsCreateAttributes` entirely: they are read-only, so they appear on the resource and
     * in no write shape.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['title'];

    /**
     * @var list<string>
     */
    public const array FILTERS = ['tracks', 'artist.name', 'title', 'rating', 'releasedAt', 'q'];

    /**
     * The members a structured filter's own value accepts.
     *
     * The top-level guard does not reach these: an array shape rejects a wrong *value* type
     * inside a nested map but accepts an unknown key there, exactly as it does at the top level,
     * and `filter(['rating' => ['minimum' => 4.0]])` would otherwise compile and filter nothing.
     *
     * @var array<string, list<string>>
     */
    public const array FILTER_MEMBERS = [
        'rating' => ['min', 'max'],
        'releasedAt' => ['min', 'max'],
    ];

    /**
     * @var list<string>
     */
    public const array SORT_TOKENS = ['title', '-title', 'releasedAt', '-releasedAt', 'status', '-status'];

    /**
     * @var list<string>
     */
    public const array FIELDS = [
        'title',
        'averageRating',
        'artwork',
        'releasedAt',
        'explicit',
        'status',
        'availableFrom',
        'availableUntil',
        'releaseInfo',
        'artist',
        'tracks',
    ];

    /**
     * The `withCount` tokens `GET /albums` advertises.
     *
     * The collection endpoint lists `tracks` and not `_self_`, even though its own parameter
     * description says `_self_` counts the collection. Generated code follows the enum, so
     * `_self_` is not offered here and `_page()->total` stays null on this endpoint.
     *
     * @var list<string>
     */
    public const array COUNT_TOKENS = ['tracks'];

    /**
     * Requesting a `withCount` token opts into the Countable profile, which the client adds to
     * `Accept` only when a request actually uses a profile-gated parameter.
     */
    public const string COUNTABLE_PROFILE = 'https://haddowg.github.io/json-api/profiles/countable/';

    public ?Artist $artist { get => $this->artist(); }

    /** @var ResourceCollection<Track> */
    public ResourceCollection $tracks { get => $this->tracks(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `albums` one
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
     * A resource identifier for an album that is not in hand, for a relationship write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }

    /**
     * A reusable projection for a write response: `include` and `fields`, and nothing a `POST`
     * would be rejected for carrying.
     *
     * @return AlbumProjection<AlbumBase>
     */
    public static function projection(): AlbumProjection
    {
        return AlbumProjection::make();
    }

    /**
     * The create input, as a builder or as the canonical DTO.
     *
     * Both arities are expressible in one signature. Passing nothing hands back a builder for
     * conditional assembly; passing an array validates its keys against the descriptor and
     * hands back the DTO the builder would have produced.
     *
     * @param AlbumCreateShape|null $input
     *
     * @return ($input is null ? AlbumCreateBuilder : AlbumCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `albums` does not have
     */
    public static function create(?array $input = null): AlbumCreateBuilder|AlbumCreate
    {
        return $input === null ? new AlbumCreateBuilder() : AlbumCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array.
     *
     * Every update member is optional, so the fluent form needs no separate builder. Each setter
     * returns a new input, which is why a discarded setter call is a static error rather than a
     * silent no-op.
     *
     * @param AlbumUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `albums` does not have
     */
    public static function update(?array $input = null): AlbumUpdate
    {
        return $input === null ? new AlbumUpdate() : AlbumUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `artist`
     */
    public function artist(): ?Artist
    {
        $raw = $this->_includedOne('artist', 'artistRef');

        return $raw === null ? null : Artist::_hydrate($raw, $this->_child('artist'));
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
}

/**
 * The resource-scoped custom actions of `albums`.
 *
 * Behind a namespace rather than on the handle directly, because an action name is free to
 * collide with a relation method or a verb — `$album->tracks()` could be either without it.
 *
 * Each signature is derived from the action's own declaration, and these two are deliberately
 * different in kind. `reissue` declares `AlbumsCreateRequest` as its body and `AlbumsDocument`
 * as its response, so it takes the three write forms and returns a resource. `artwork` accepts
 * a multipart form or any image and answers `204`, so it takes the media type to send and a body
 * typed by it, and returns nothing. The body is nullable because its request body is
 * `required: false`.
 *
 * @phpstan-import-type AlbumCreateShape from Album
 *
 * @generated from `POST /albums/{id}/-actions/reissue` and `POST /albums/{id}/-actions/artwork`
 */
final class AlbumActions
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * Artwork: `POST /albums/{id}/-actions/artwork`. The body goes over the wire as form fields for
     * a form media type, JSON-encoded for a JSON one and verbatim otherwise, as `$contentType`,
     * which is one of `multipart/form-data` or `image/*`. The body is optional, so it may be left
     * out.
     *
     * @template TContentType of 'multipart/form-data'|non-empty-string
     *
     * @param TContentType                                                                                                          $contentType
     * @param (TContentType is 'multipart/form-data' ? array<string, string|int|float|bool|FilePart> : StreamInterface|string)|null $body
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when no album has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function artwork(
        string $contentType,
        StreamInterface|string|array|null $body = null,
    ): void {
        if (!\in_array($contentType, ['multipart/form-data'], true) && !\str_starts_with($contentType, 'image/')) {
            throw new \InvalidArgumentException(\sprintf('The "artwork" action takes its body as one of multipart/form-data, image/*, not %s.', $contentType));
        }

        $request = Request::post('/albums/' . $this->id . '/-actions/artwork');

        $request = match (true) {
            $body === null => $request,
            $contentType === 'multipart/form-data' && \is_array($body) => $request->withMultipartBody(FormEncoding::multipartFields($body)),
            $body instanceof StreamInterface, \is_string($body) => $request->withRawBody($body, $contentType),
            default => throw new \InvalidArgumentException(\sprintf('The "artwork" action cannot send this body as %s.', $contentType)),
        };

        $this->fetcher->send($request);
    }

    /**
     * Reissue the album.
     *
     * @template TProj of object
     *
     * @param AlbumCreate|AlbumCreateBuilder|AlbumCreateShape                                    $input
     * @param (callable(AlbumProjection<AlbumBase>): AlbumProjection<TProj>)|AlbumProjection<TProj>|null $projection
     *
     * @return ($projection is null ? AlbumBase : AlbumBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when no album has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function reissue(
        AlbumCreate|AlbumCreateBuilder|array $input,
        callable|AlbumProjection|null $projection = null,
    ): AlbumBase {
        $document = match (true) {
            $input instanceof AlbumCreate => $input,
            $input instanceof AlbumCreateBuilder => $input->build(),
            default => AlbumCreate::from($input),
        };

        $query = AlbumProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post(
                $query->appendTo('/albums/' . $this->id . '/-actions/reissue'),
                \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The reissue response', 'data');

        /** @var AlbumBase&TProj */
        return $context->hydrate($raw, Album::_hydrate(...));
    }
}

/**
 * The `artist` relationship of one album.
 *
 * A to-one, and the generated verbs say so: `PATCH /albums/{id}/relationships/artist` is
 * declared and `POST`/`DELETE` are not, so `set()` exists and `add()` does not. Calling
 * `add()` is an undefined method rather than a runtime 404 from code that compiled.
 *
 * Both reads exist because both endpoints do. `related()` is `/albums/{id}/artist` and `get()`
 * is `/albums/{id}/relationships/artist`; the two suppression flags are independent, so a
 * server that switched either off would have that method absent and the other still present.
 *
 * @generated from `/albums/{id}/artist` and `/albums/{id}/relationships/artist`
 */
final class AlbumArtistRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone: `{type, id}` or nothing, without fetching the artist.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no album has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/albums/' . $this->id . '/relationships/artist'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "artist" linkage');
    }

    /**
     * The artist itself.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no album has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): ?Artist
    {
        $context = $this->fetcher->read(Request::get('/albums/' . $this->id . '/artist'));
        $raw = $context->primary();

        return $raw === null ? null : Artist::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at an artist, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no album has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function set(Artist|Identifier|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/albums/' . $this->id . '/relationships/artist',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * A raw JSON response body, narrowed member by member: an optional member of the wrong type is
 * dropped rather than trusted, and a required one throws.
 *
 * @generated from `components.schemas.AlbumCatalogueStats`
 */
final class AlbumCatalogueStats
{
    /**
     * @param int<0, max> $published
     * @param int<0, max> $unpublished
     */
    public function __construct(
        public readonly int $published,
        public readonly int $unpublished,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $published = $raw['published'] ?? null;
        $unpublished = $raw['unpublished'] ?? null;

        $publishedValue = null;
        if (!\is_int($published)) {
            throw MalformedDocument::unexpectedMember('The AlbumCatalogueStats body', 'published', 'an integer');
        }

        /** @var int<0, max> $published */
        $publishedValue = $published;

        $unpublishedValue = null;
        if (!\is_int($unpublished)) {
            throw MalformedDocument::unexpectedMember('The AlbumCatalogueStats body', 'unpublished', 'an integer');
        }

        /** @var int<0, max> $unpublished */
        $unpublishedValue = $unpublished;

        return new self($publishedValue, $unpublishedValue);
    }
}

/**
 * The collection-scoped custom actions of `albums`, reached through the type accessor. Each
 * signature is derived from the action's own request body and response.
 *
 * @generated from `GET /albums/-actions/export` and `POST /albums/-actions/import` and `POST /albums/-actions/summary`
 */
final class AlbumCollectionActions
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Export the album catalogue: `GET /albums/-actions/export`. It declares no request body, so it
     * takes no input.
     *
     * Exports every album in the format the Accept header asks for: a JSON:API meta document with
     * counts, a CSV of ids and titles, a PDF report, or the counts as plain JSON.
     *
     * It answers with JSON:API unless `$accept` asks for `text/csv`, `application/pdf` or
     * `application/json` instead. An error is always a JSON:API document.
     *
     * @param 'application/vnd.api+json'|'text/csv'|'application/pdf'|'application/json' $accept the media type to ask for; JSON:API unless another is named
     *
     * @return ($accept is 'text/csv' ? string : ($accept is 'application/pdf' ? StreamInterface : ($accept is 'application/json' ? AlbumCatalogueStats : array<string, mixed>)))
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when the endpoint does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when a JSON body is not what its schema says
     */
    public function export(string $accept = 'application/vnd.api+json'): array|string|StreamInterface|AlbumCatalogueStats
    {
        $request = Request::get('/albums/-actions/export');

        if ($accept === 'text/csv') {
            return $this->fetcher->text($request->withAccept('text/csv'));
        }

        if ($accept === 'application/pdf') {
            return $this->fetcher->stream($request->withAccept('application/pdf'));
        }

        if ($accept === 'application/json') {
            return AlbumCatalogueStats::_from($this->fetcher->jsonObject($request->withAccept('application/json')));
        }

        return $this->fetcher->meta($request);
    }

    /**
     * Import albums from a file: `POST /albums/-actions/import`. The body goes over the wire as
     * form fields for a form media type, JSON-encoded for a JSON one and verbatim otherwise, as
     * `$contentType`, which is one of `text/csv`, `image/*`, `multipart/form-data` or
     * `application/json`. The body is optional, so it may be left out.
     *
     * Creates albums from a CSV export, a scanned sleeve image, a multipart form upload or a JSON
     * list of rows. The response reports the media type the server read.
     *
     * @template TContentType of 'text/csv'|'multipart/form-data'|'application/json'|non-empty-string
     *
     * @param TContentType                                                                                                                                                                                                                                                           $contentType
     * @param (TContentType is 'text/csv' ? StreamInterface|string : (TContentType is 'multipart/form-data' ? array<string, string|int|float|bool|FilePart> : (TContentType is 'application/json' ? list<array{title: string, releasedOn?: string}> : StreamInterface|string)))|null $body
     *
     * @return array<string, mixed>
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when the endpoint does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function import(
        string $contentType,
        StreamInterface|string|array|null $body = null,
    ): array {
        if (!\in_array($contentType, ['text/csv', 'multipart/form-data', 'application/json'], true) && !\str_starts_with($contentType, 'image/')) {
            throw new \InvalidArgumentException(\sprintf('The "import" action takes its body as one of text/csv, image/*, multipart/form-data, application/json, not %s.', $contentType));
        }

        $request = Request::post('/albums/-actions/import');

        $request = match (true) {
            $body === null => $request,
            $contentType === 'multipart/form-data' && \is_array($body) => $request->withMultipartBody(FormEncoding::multipartFields($body)),
            $contentType === 'application/json' => $request->withRawBody(\json_encode($body, \JSON_THROW_ON_ERROR), $contentType),
            $body instanceof StreamInterface, \is_string($body) => $request->withRawBody($body, $contentType),
            default => throw new \InvalidArgumentException(\sprintf('The "import" action cannot send this body as %s.', $contentType)),
        };

        return $this->fetcher->meta($request);
    }

    /**
     * Summary: `POST /albums/-actions/summary`. It declares no request body, so it takes no input.
     *
     * @return array<string, mixed>
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when the endpoint does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function summary(): array
    {
        return $this->fetcher->meta(Request::post('/albums/-actions/summary'));
    }
}

/**
 * The canonical create input for `albums`, as named arguments.
 *
 * This is the form the docs lead with, because it is the only one of the three with no blind
 * spot: a missing required member is a static error *and* an `ArgumentCountError`, a typo is an
 * unknown named argument whether the member is required or optional, and every value type is
 * checked. The builder cannot statically require that a setter was called; the array shape
 * accepts any unknown *optional* key. Both are backstopped at runtime, and neither needs to be
 * when a DTO is used.
 *
 * `Missing` defaults every optional member because PHP has no way to say "this argument was not
 * passed", and JSON:API needs absent to stay distinct from an explicit `null` — an omitted
 * member leaves the server value alone, a `null` member clears it. The serialiser drops every
 * member still holding the sentinel.
 *
 * `averageRating` and `artwork` are absent here because they are absent from
 * `AlbumsCreateAttributes`: the server declares them read-only, so they are on the resource and
 * in no write shape.
 *
 * Required members take no default, so the declaration order is required-first. Named arguments
 * mean that never binds a caller.
 *
 * @phpstan-import-type AlbumCreateShape from Album
 *
 * @generated from `components.schemas.AlbumsCreateRequest`
 */
final class AlbumCreate
{
    /**
     * @param non-empty-string                                             $title
     * @param array{label?: string, catalogueNumber?: string}|Missing|null $releaseInfo
     * @param Artist|Identifier|Missing|null                               $artist
     * @param list<Identifier|Track>|Missing                               $tracks
     */
    public function __construct(
        public readonly string $title,
        public readonly \DateTimeImmutable|Missing $releasedAt = Missing::Value,
        public readonly bool|Missing $explicit = Missing::Value,
        public readonly AlbumStatus|Missing $status = Missing::Value,
        public readonly \DateTimeImmutable|Missing|null $availableFrom = Missing::Value,
        public readonly \DateTimeImmutable|Missing|null $availableUntil = Missing::Value,
        public readonly array|Missing|null $releaseInfo = Missing::Value,
        public readonly Artist|Identifier|Missing|null $artist = Missing::Value,
        public readonly array|Missing $tracks = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * The check is the point. An all-optional array shape accepts any unknown key, so
     * `['titel' => 'x']` type-checks, sends nothing and silently no-ops — the only way to lose
     * data through this API, and the reason this method exists rather than the array being
     * passed straight through.
     *
     * @param AlbumCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `albums`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Album::WRITABLE, true)) {
                throw UnknownAttributeException::for(AlbumBase::TYPE, (string) $member, Album::WRITABLE);
            }
        }

        // `array_key_exists` and not `??`: a member holding an explicit `null` is present and
        // clears the server value, and `??` would fold it into the sentinel that means absent —
        // silently turning "clear this" into "leave it alone", which is the one distinction the
        // sentinel exists for.
        return new self(
            title: $input['title'],
            releasedAt: Coerce::dateTime(\array_key_exists('releasedAt', $input) ? $input['releasedAt'] : Missing::Value),
            explicit: \array_key_exists('explicit', $input) ? $input['explicit'] : Missing::Value,
            status: Coerce::enum(AlbumStatus::class, \array_key_exists('status', $input) ? $input['status'] : Missing::Value),
            availableFrom: Coerce::nullableDateTime(\array_key_exists('availableFrom', $input) ? $input['availableFrom'] : Missing::Value),
            availableUntil: Coerce::nullableDateTime(\array_key_exists('availableUntil', $input) ? $input['availableUntil'] : Missing::Value),
            releaseInfo: \array_key_exists('releaseInfo', $input) ? $input['releaseInfo'] : Missing::Value,
            artist: \array_key_exists('artist', $input) ? $input['artist'] : Missing::Value,
            tracks: \array_key_exists('tracks', $input) ? $input['tracks'] : Missing::Value,
        );
    }

    /**
     * The `POST /albums` request document.
     *
     * One serialisation path for all three input forms: the builder's `build()` produces this
     * DTO and the array goes through `from()`, so nothing else ever writes a document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => AlbumBase::TYPE];

        // Each member's wire format is known at generation time, which is where that knowledge
        // belongs: `releasedAt` is `format: date-time` and `availableFrom` is `format: date`,
        // and the runtime never has to guess between them.
        $attributes = ['title' => $this->title];

        if (!$this->releasedAt instanceof Missing) {
            $attributes['releasedAt'] = $this->releasedAt->format(\DateTimeInterface::ATOM);
        }

        if (!$this->explicit instanceof Missing) {
            $attributes['explicit'] = $this->explicit;
        }

        if (!$this->status instanceof Missing) {
            $attributes['status'] = $this->status->value;
        }

        if (!$this->availableFrom instanceof Missing) {
            $attributes['availableFrom'] = $this->availableFrom?->format('Y-m-d');
        }

        if (!$this->availableUntil instanceof Missing) {
            $attributes['availableUntil'] = $this->availableUntil?->format('Y-m-d');
        }

        if (!$this->releaseInfo instanceof Missing) {
            $attributes['releaseInfo'] = $this->releaseInfo;
        }

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->artist instanceof Missing) {
            $relationships['artist'] = ['data' => Coerce::linkage($this->artist)];
        }

        if (!$this->tracks instanceof Missing) {
            $relationships['tracks'] = ['data' => Coerce::linkageList($this->tracks)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `albums`, for assembly that is conditional.
 *
 * Its reason to exist is `when()`/`unless()`/`tap()`: building a write over branches without
 * an intermediate array, with the callback parameter typed so a typo'd setter and a wrong value
 * type inside the closure are both caught. Where nothing is conditional the DTO is strictly
 * better, and `build()` is what backstops the one thing this form cannot check statically.
 *
 * Mutable and self-returning, unlike the immutable query builders. Write builders narrow
 * nothing, so there is no projection to lose and the callback's return can simply be honoured.
 *
 * @generated from `components.schemas.AlbumsCreateRequest`
 */
final class AlbumCreateBuilder
{
    use Conditionable;

    /**
     * @var non-empty-string|Missing
     */
    private string|Missing $title = Missing::Value;

    private \DateTimeImmutable|Missing $releasedAt = Missing::Value;

    private bool|Missing $explicit = Missing::Value;

    private AlbumStatus|Missing $status = Missing::Value;

    private \DateTimeImmutable|Missing|null $availableFrom = Missing::Value;

    private \DateTimeImmutable|Missing|null $availableUntil = Missing::Value;

    /**
     * @var array{label?: string, catalogueNumber?: string}|Missing|null
     */
    private array|Missing|null $releaseInfo = Missing::Value;

    private Artist|Identifier|Missing|null $artist = Missing::Value;

    /**
     * @var list<Identifier|Track>|Missing
     */
    private array|Missing $tracks = Missing::Value;

    /**
     * @param non-empty-string $title
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function releasedAt(\DateTimeImmutable $releasedAt): self
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    public function explicit(bool $explicit = true): self
    {
        $this->explicit = $explicit;

        return $this;
    }

    public function status(AlbumStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function availableFrom(?\DateTimeImmutable $availableFrom): self
    {
        $this->availableFrom = $availableFrom;

        return $this;
    }

    public function availableUntil(?\DateTimeImmutable $availableUntil): self
    {
        $this->availableUntil = $availableUntil;

        return $this;
    }

    /**
     * @param array{label?: string, catalogueNumber?: string}|null $releaseInfo
     */
    public function releaseInfo(?array $releaseInfo): self
    {
        $this->releaseInfo = $releaseInfo;

        return $this;
    }

    /**
     * A relation setter takes the resource itself or a bare reference, so a value in hand and
     * an id you happen to know are the same call.
     */
    public function artist(Artist|Identifier|null $artist): self
    {
        $this->artist = $artist;

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
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was
     *                                        never set — the builder's one blind spot, which no
     *                                        amount of typing can close
     */
    public function build(): AlbumCreate
    {
        // One guard per required member rather than a collected list: narrowing has to survive
        // into the constructor call, and PHPStan cannot connect `$missing !== []` back to the
        // property it was collected from.
        if ($this->title instanceof Missing) {
            throw MissingRequiredMemberException::for(AlbumBase::TYPE, ['title']);
        }

        return new AlbumCreate(
            title: $this->title,
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }
}

/**
 * One album by id, before anything has been fetched.
 *
 * An inert handle, which is what makes it safe to hang the relationship writes and the
 * resource-scoped actions off it: `$client->albums->id('1')` performs no IO, and every method
 * on it is an explicit call.
 *
 * `get()` takes a projection rather than a query builder, and that follows the document rather
 * than a preference: `GET /albums/{id}` advertises `include` and `fields` and no other query
 * parameter, which is exactly what {@see AlbumProjection} is.
 *
 * @phpstan-import-type AlbumUpdateShape from Album
 *
 * @generated from `GET`, `PATCH` and `DELETE /albums/{id}`
 */
final class AlbumHandle
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    /**
     * Fetch the album.
     *
     * @template TProj of object
     *
     * @param (callable(AlbumProjection<AlbumBase>): AlbumProjection<TProj>)|AlbumProjection<TProj>|null $projection
     *
     * @return ($projection is null ? AlbumBase : AlbumBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no album has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|AlbumProjection|null $projection = null): AlbumBase
    {
        $query = AlbumProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/albums/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The albums response', 'data');

        /** @var AlbumBase&TProj */
        return $context->hydrate($raw, Album::_hydrate(...));
    }

    /**
     * Patch the album, in any of the three input forms.
     *
     * The id comes from the handle and is written into the document, so the body and the URL
     * cannot disagree.
     *
     * @template TProj of object
     *
     * @param AlbumUpdate|AlbumUpdateShape                                                       $input
     * @param (callable(AlbumProjection<AlbumBase>): AlbumProjection<TProj>)|AlbumProjection<TProj>|null $projection
     *
     * @return ($projection is null ? AlbumBase : AlbumBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no album has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function update(
        AlbumUpdate|array $input,
        callable|AlbumProjection|null $projection = null,
    ): AlbumBase {
        $document = match (true) {
            $input instanceof AlbumUpdate => $input,
            default => AlbumUpdate::from($input),
        };

        $query = AlbumProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/albums/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var AlbumBase&TProj */
        return $context->hydrate($raw, Album::_hydrate(...));
    }

    /**
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no album has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/albums/' . $this->id));
    }

    /**
     * The `artist` relationship: a to-one, so it has `set()` and no `add()`.
     */
    public function artist(): AlbumArtistRelation
    {
        return new AlbumArtistRelation($this->fetcher, $this->id);
    }

    /**
     * The `tracks` relationship: a to-many whose endpoint declares `POST`, `DELETE` and `PATCH`,
     * so all three verbs are generated.
     */
    public function tracks(): AlbumTracksRelation
    {
        return new AlbumTracksRelation($this->fetcher, $this->id);
    }

    /**
     * Resource-scoped custom actions.
     */
    public function actions(): AlbumActions
    {
        return new AlbumActions($this->fetcher, $this->id);
    }
}

/**
 * What an `albums` write response should bring back: `include` and `fields`, and nothing else.
 *
 * Projection is extracted from the query builder rather than reused because a write response
 * honours exactly these two parameters. Under the server's strict query validation a stray
 * `sort` on a `POST` is a `400`, not something quietly ignored — so `sort()`, `filter()` and
 * `page()` are not merely unused here, they are undefined methods.
 *
 * Reusable: build one and hand it to every write that should come back the same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /albums/{id}`
 */
final class AlbumProjection extends Projection
{
    /** @use AlbumProjecting<TProj> */
    use AlbumProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<AlbumBase>
     */
    public static function make(): self
    {
        /** @var self<AlbumBase> */
        return new self();
    }

    /**
     * Resolve either projection form to the query parameters a write response carries.
     *
     * Writes accept a closure or a prebuilt projection, and both end up here so every write
     * entry point shares one resolution rather than repeating it.
     *
     * @template T of object
     *
     * @param (callable(self<AlbumBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /albums`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not. `page(int, ?int)` exists
 * because the operation declares `page[number]` and `page[size]`; a cursor-paginated endpoint
 * would get `after`/`before` instead, and an unpaginated one would get neither along with no
 * `_page()` on its results.
 *
 * Filters come in three doors on purpose. `whereTitle()` catches a typo'd filter *name* as an
 * undefined method. `filter()` takes the typed shape. `filterRaw()` is the explicit loose door.
 * They are separate methods because a single parameter typed
 * `AlbumFilterShape|array<string, mixed>` silently accepts anything — the permissive branch
 * swallows the shape and the checking is worth exactly nothing. Both array doors still validate
 * names at runtime: every filter is optional, so a typo lands in the one blind spot the static
 * types have, and a quietly unfiltered collection looks like data rather than a bug.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type AlbumCountToken from Album
 * @phpstan-import-type AlbumDateRangeShape from Album
 * @phpstan-import-type AlbumFilterShape from Album
 * @phpstan-import-type AlbumIncludePath from Album
 * @phpstan-import-type AlbumRangeShape from Album
 * @phpstan-import-type AlbumSortToken from Album
 *
 * @generated from `GET /albums` (operationId `fetchCollection.albums`)
 */
final class AlbumQuery extends Query
{
    /** @use AlbumProjecting<TProj> */
    use AlbumProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Filter by `tracks`.
     *
     * @return static
     */
    public function whereTracks(string $value): static
    {
        return $this->filtering(['tracks' => $value]);
    }

    /**
     * Filter by `artist.name`.
     *
     * @return static
     */
    public function whereArtistName(string $value): static
    {
        return $this->filtering(['artist.name' => $value]);
    }

    /**
     * Matches values containing the given substring.
     *
     * @return static
     */
    public function whereTitle(string $value): static
    {
        return $this->filtering(['title' => $value]);
    }

    /**
     * Matches values within the given inclusive numeric range (min/max, either optional).
     *
     * @param AlbumRangeShape $range
     *
     * @return static
     */
    public function whereRating(array $range): static
    {
        return $this->filtering(['rating' => $range]);
    }

    /**
     * Matches values within the given inclusive date-time range (min/max ISO-8601, either
     * optional).
     *
     * @param AlbumDateRangeShape $range
     *
     * @return static
     */
    public function whereReleasedAt(array $range): static
    {
        return $this->filtering(['releasedAt' => $range]);
    }

    /**
     * Case-insensitive substring search across title.
     *
     * @return static
     */
    public function whereQ(string $value): static
    {
        return $this->filtering(['q' => $value]);
    }

    /**
     * The typed array door: values are checked, including nested ranges, and names are checked
     * at runtime.
     *
     * @param AlbumFilterShape $filter
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
     * Sort by the endpoint's own tokens, in precedence order.
     *
     * Argument order is precedence, so nothing has to be translated between the call and the
     * wire. A typo'd field, a real-but-unsortable one, and `+releasedAt` are all rejected before
     * the request exists, and a multi-argument mistake names the offending position.
     *
     * @param AlbumSortToken ...$tokens
     *
     * @return static
     */
    public function sort(string ...$tokens): static
    {
        return $this->sorting(\array_values($tokens));
    }

    /**
     * The loose sort door, for tokens assembled at runtime — a `list<string>` can never satisfy
     * a literal union, which is the whole reason this exists.
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
            if (!\in_array($token, Album::SORT_TOKENS, true)) {
                throw UnknownSortException::for($token, Album::SORT_TOKENS);
            }
        }

        return $this->sorting($tokens);
    }

    /**
     * Count related resources without including them, under the Countable profile.
     *
     * `GET /albums` advertises `tracks` and nothing else. Its own parameter description mentions
     * `_self_` — the token that puts a collection total in `meta.page` — but the enum does not
     * list it, so it is not generated and `_page()->total` stays null on this endpoint.
     *
     * A counted relation is still not an included one: `$album->tracks` throws, and the count is
     * read through `$album->_rel('tracks')->total()`.
     *
     * @param list<AlbumCountToken> $tokens
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
     * Paginator parameters assembled at runtime (`['number' => 2, 'size' => 50]`). Public here
     * because `GET /albums` is paginated; the runtime keeps it protected.
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
     * @throws BadRequest          when a query parameter is rejected
     * @throws Unauthorized        when credentials are missing or invalid
     * @throws Forbidden           when the read is not permitted
     * @throws NotAcceptable       when the negotiated media type cannot be satisfied
     * @throws ServerError         when the server fails
     * @throws TransportException  when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query), $query);
    }

    /**
     * The first album the read matches, fetching a single-resource page rather than a whole one.
     *
     * @return TProj|null
     *
     * @throws BadRequest          when a query parameter is rejected
     * @throws Unauthorized        when credentials are missing or invalid
     * @throws Forbidden           when the read is not permitted
     * @throws NotAcceptable       when the negotiated media type cannot be satisfied
     * @throws ServerError         when the server fails
     * @throws TransportException  when no response is received at all
     */
    public function first(): ?object
    {
        return $this->page(1, 1)->get()->first();
    }

    /**
     * @param AlbumFilterShape|array<string, mixed> $filter
     *
     * @return array<string, mixed>
     *
     * @throws UnknownFilterException
     */
    private static function guardFilters(array $filter): array
    {
        foreach ($filter as $name => $value) {
            $name = (string) $name;

            if (!\in_array($name, Album::FILTERS, true)) {
                throw UnknownFilterException::for($name, Album::FILTERS);
            }

            $members = Album::FILTER_MEMBERS[$name] ?? null;

            if ($members === null || !\is_array($value)) {
                continue;
            }

            $qualified = \array_map(static fn(string $member): string => $name . '[' . $member . ']', $members);

            foreach (\array_keys($value) as $member) {
                if (!\in_array((string) $member, $members, true)) {
                    throw UnknownFilterException::for($name . '[' . (string) $member . ']', $qualified);
                }
            }
        }

        return $filter;
    }

    /**
     * The Countable profile is negotiated only when a request actually uses a `withCount`
     * parameter, which is what `x-profile` on that parameter already states.
     */
    private function request(ReadQuery $query): Request
    {
        $request = Request::get($query->appendTo('/albums'));

        return $query->withCount === [] ? $request : $request->withProfiles([Album::COUNTABLE_PROFILE]);
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $albums = [];

        foreach ($context->primaryList() as $raw) {
            $albums[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $albums,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid.
     *
     * The runtime always builds an {@see Album}, and `Album` implements every marker — but
     * `TProj` is a template parameter, so no bound can prove `Album` satisfies it, and typing
     * the result `Album&TProj` would collapse to plain `Album` and undo every narrowing.
     * {@see ResourceContext::hydrate()} erases the concrete class so the assertion is legal;
     * one of these per generated class is the whole cost.
     *
     * @param array<string, mixed> $raw
     *
     * @return TProj
     */
    private function hydrate(array $raw, ResourceContext $context): object
    {
        /** @var TProj */
        return $context->hydrate($raw, Album::_hydrate(...));
    }
}

/**
 * The `tracks` relationship of one album.
 *
 * A to-many whose relationship endpoint declares `POST`, `DELETE` and `PATCH`, so all three
 * verbs are generated. `tracks/{id}/relationships/playlists` in the same document declares only
 * `POST` and `DELETE`, and its handle would have no `replace()` at all — the rule is the
 * contract, not the cardinality.
 *
 * Both reads are paginated because both endpoints advertise `page[number]` and `page[size]`,
 * which is the relation's own paginator resolved ahead of the related type's.
 *
 * @generated from `/albums/{id}/tracks` and `/albums/{id}/relationships/tracks`
 */
final class AlbumTracksRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone, a page at a time.
     *
     * @return PaginatedCollection<Identifier, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no album has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/albums/' . $this->id . '/relationships/tracks')),
        );
    }

    /**
     * The tracks themselves, a page at a time.
     *
     * The endpoint also advertises `filter`, `sort`, `include`, `fields` and `withCount`. Those
     * need a query builder scoped to the relation, which is a generated class this artefact does
     * not carry — see the README.
     *
     * @return PaginatedCollection<Track, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no album has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/albums/' . $this->id . '/tracks')));
    }

    /**
     * Add tracks to the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Track> $tracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no album has this id
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
            '/albums/' . $this->id . '/relationships/tracks',
            \json_encode(['data' => Coerce::linkageList($tracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove tracks from the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Track> $tracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no album has this id
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
            '/albums/' . $this->id . '/relationships/tracks',
            \json_encode(['data' => Coerce::linkageList($tracks)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Replace the whole relationship.
     *
     * @param list<Identifier|Track> $tracks
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no album has this id
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
            '/albums/' . $this->id . '/relationships/tracks',
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
 * The canonical update input for `albums`: every member optional, which is what a JSON:API
 * `PATCH` is.
 *
 * `AlbumsUpdateAttributes` declares no `required` list, so nothing here takes a value and the
 * whole shape sits in the array door's one blind spot — a typo on an optional key compiles,
 * sends nothing, and looks like success. `from()` is the guard that closes it, and on a
 * type whose write shape is entirely optional it is not a nicety.
 *
 * The id is not a member: it comes from the handle the update is called on
 * (`$client->albums->id('1')->update(…)`), so it cannot disagree with the URL.
 *
 * @phpstan-import-type AlbumUpdateShape from Album
 *
 * @generated from `components.schemas.AlbumsUpdateRequest`
 */
final class AlbumUpdate
{
    use ImmutableConditionable;

    /**
     * @param non-empty-string|Missing                                     $title
     * @param array{label?: string, catalogueNumber?: string}|Missing|null $releaseInfo
     * @param Artist|Identifier|Missing|null                               $artist
     * @param list<Identifier|Track>|Missing                               $tracks
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $title = Missing::Value,
        public readonly \DateTimeImmutable|Missing $releasedAt = Missing::Value,
        public readonly bool|Missing $explicit = Missing::Value,
        public readonly AlbumStatus|Missing $status = Missing::Value,
        public readonly \DateTimeImmutable|Missing|null $availableFrom = Missing::Value,
        public readonly \DateTimeImmutable|Missing|null $availableUntil = Missing::Value,
        public readonly array|Missing|null $releaseInfo = Missing::Value,
        public readonly Artist|Identifier|Missing|null $artist = Missing::Value,
        public readonly array|Missing $tracks = Missing::Value,
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
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function releasedAt(\DateTimeImmutable $releasedAt): self
    {
        return new self(
            title: $this->title,
            releasedAt: $releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function explicit(bool $explicit = true): self
    {
        return new self(
            title: $this->title,
            releasedAt: $this->releasedAt,
            explicit: $explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function status(AlbumStatus $status): self
    {
        return new self(
            title: $this->title,
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function availableFrom(?\DateTimeImmutable $availableFrom): self
    {
        return new self(
            title: $this->title,
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function availableUntil(?\DateTimeImmutable $availableUntil): self
    {
        return new self(
            title: $this->title,
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }

    /**
     * @param array{label?: string, catalogueNumber?: string}|null $releaseInfo
     *
     * @phpstan-pure
     */
    public function releaseInfo(?array $releaseInfo): self
    {
        return new self(
            title: $this->title,
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $releaseInfo,
            artist: $this->artist,
            tracks: $this->tracks,
        );
    }

    /**
     * @phpstan-pure
     */
    public function artist(Artist|Identifier|null $artist): self
    {
        return new self(
            title: $this->title,
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $artist,
            tracks: $this->tracks,
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
            releasedAt: $this->releasedAt,
            explicit: $this->explicit,
            status: $this->status,
            availableFrom: $this->availableFrom,
            availableUntil: $this->availableUntil,
            releaseInfo: $this->releaseInfo,
            artist: $this->artist,
            tracks: $tracks,
        );
    }

    /**
     * The input itself. Present so an update assembled fluently reads the same as a create one,
     * which needs a separate builder to tolerate a missing required member until the end.
     *
     * @phpstan-pure
     */
    public function build(): self
    {
        return $this;
    }

    /**
     * @param AlbumUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `albums`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Album::WRITABLE, true)) {
                throw UnknownAttributeException::for(AlbumBase::TYPE, (string) $member, Album::WRITABLE);
            }
        }

        // `array_key_exists` and not `??`: a member holding an explicit `null` is present and
        // clears the server value, and `??` would fold it into the sentinel that means absent —
        // silently turning "clear this" into "leave it alone", which is the one distinction the
        // sentinel exists for. On an all-optional patch shape this is the difference between a
        // working `PATCH` and a no-op.
        return new self(
            title: \array_key_exists('title', $input) ? $input['title'] : Missing::Value,
            releasedAt: Coerce::dateTime(\array_key_exists('releasedAt', $input) ? $input['releasedAt'] : Missing::Value),
            explicit: \array_key_exists('explicit', $input) ? $input['explicit'] : Missing::Value,
            status: Coerce::enum(AlbumStatus::class, \array_key_exists('status', $input) ? $input['status'] : Missing::Value),
            availableFrom: Coerce::nullableDateTime(\array_key_exists('availableFrom', $input) ? $input['availableFrom'] : Missing::Value),
            availableUntil: Coerce::nullableDateTime(\array_key_exists('availableUntil', $input) ? $input['availableUntil'] : Missing::Value),
            releaseInfo: \array_key_exists('releaseInfo', $input) ? $input['releaseInfo'] : Missing::Value,
            artist: \array_key_exists('artist', $input) ? $input['artist'] : Missing::Value,
            tracks: \array_key_exists('tracks', $input) ? $input['tracks'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /albums/{id}` request document.
     *
     * Only members that were set reach the wire. A member holding `null` is sent as `null` and
     * clears the server value; a member holding {@see Missing} is not sent at all and leaves it
     * alone. That distinction is the whole reason the sentinel exists.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => AlbumBase::TYPE, 'id' => $id];
        $attributes = [];

        if (!$this->title instanceof Missing) {
            $attributes['title'] = $this->title;
        }

        if (!$this->releasedAt instanceof Missing) {
            $attributes['releasedAt'] = $this->releasedAt->format(\DateTimeInterface::ATOM);
        }

        if (!$this->explicit instanceof Missing) {
            $attributes['explicit'] = $this->explicit;
        }

        if (!$this->status instanceof Missing) {
            $attributes['status'] = $this->status->value;
        }

        if (!$this->availableFrom instanceof Missing) {
            $attributes['availableFrom'] = $this->availableFrom?->format('Y-m-d');
        }

        if (!$this->availableUntil instanceof Missing) {
            $attributes['availableUntil'] = $this->availableUntil?->format('Y-m-d');
        }

        if (!$this->releaseInfo instanceof Missing) {
            $attributes['releaseInfo'] = $this->releaseInfo;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->artist instanceof Missing) {
            $relationships['artist'] = ['data' => Coerce::linkage($this->artist)];
        }

        if (!$this->tracks instanceof Missing) {
            $relationships['tracks'] = ['data' => Coerce::linkageList($this->tracks)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * Everything the `albums` collection endpoint offers.
 *
 * `create()` takes an id nowhere, because `AlbumsCreateRequest` declares `"id": false` —
 * client-generated ids are forbidden for this type, so the parameter is not absent by
 * convention, it is absent because the contract says so. A type declaring `clientId: optional`
 * gets `?string $id = null` and one declaring `required` gets it without a default.
 *
 * @phpstan-import-type AlbumCreateShape from Album
 *
 * @generated from `GET /albums` and `POST /albums`
 */
final class Albums
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the collection, unprojected and unfiltered.
     *
     * @return AlbumQuery<AlbumBase>
     */
    public function query(): AlbumQuery
    {
        /** @var AlbumQuery<AlbumBase> */
        return new AlbumQuery($this->fetcher);
    }

    /**
     * A handle on one album: reads, the update, the delete, the relationship writes and the
     * resource-scoped actions, none of which have been performed yet.
     */
    public function id(string $id): AlbumHandle
    {
        return new AlbumHandle($this->fetcher, $id);
    }

    /**
     * Create an album, in any of the three input forms.
     *
     * Always executes, always returns the resource. The optional projection shapes what comes
     * back and accepts a closure or a prebuilt {@see AlbumProjection} — the closure form reads
     * better inline, the object form is reusable across writes.
     *
     * The conditional return type is not decoration. A plain `@return AlbumBase&TProj` fails
     * with `Unable to resolve the template type TProj` when no projection is passed, because
     * there is then nothing to infer it from.
     *
     * @template TProj of object
     *
     * @param AlbumCreate|AlbumCreateBuilder|AlbumCreateShape                                    $input
     * @param (callable(AlbumProjection<AlbumBase>): AlbumProjection<TProj>)|AlbumProjection<TProj>|null $projection
     *
     * @return ($projection is null ? AlbumBase : AlbumBase&TProj)
     *
     * @throws BadRequest            when the request is malformed
     * @throws Unauthorized          when credentials are missing or invalid
     * @throws Forbidden             when the create is not permitted
     * @throws NotFound              when the endpoint does not exist
     * @throws NotAcceptable         when the negotiated media type cannot be satisfied
     * @throws Conflict              when the document conflicts with the endpoint
     * @throws UnsupportedMediaType  when the `Content-Type` is refused
     * @throws ValidationFailed      when the document fails validation
     * @throws ServerError           when the server fails
     * @throws TransportException    when no response is received at all
     * @throws MalformedDocument     when the response carries no primary resource
     */
    public function create(
        AlbumCreate|AlbumCreateBuilder|array $input,
        callable|AlbumProjection|null $projection = null,
    ): AlbumBase {
        $document = match (true) {
            $input instanceof AlbumCreate => $input,
            $input instanceof AlbumCreateBuilder => $input->build(),
            default => AlbumCreate::from($input),
        };

        $query = AlbumProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/albums'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var AlbumBase&TProj */
        return $context->hydrate($raw, Album::_hydrate(...));
    }

    /**
     * Collection-scoped custom actions, behind a namespace because an action name can collide
     * with a relation method or a verb.
     */
    public function actions(): AlbumCollectionActions
    {
        return new AlbumCollectionActions($this->fetcher);
    }
}
