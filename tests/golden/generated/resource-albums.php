<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Coerce;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\ImmutableConditionable;
use haddowg\JsonApiClient\Support\Missing;

/**
 * The `artist` relation of an `albums` resource, hydrated.
 *
 * A marker carries no implementation. `withArtist()` intersects it into a query's projection, which
 * is what makes `artist()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.AlbumsResource.properties.relationships.artist`
 */
interface AlbumHasArtist
{
    /**
     * The related `artists` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `artist`
     */
    public function artist(): ?Artist;

    public ?Artist $artist { get; }
}

/**
 * The `tracks` relation of an `albums` resource, hydrated.
 *
 * A marker carries no implementation. `withTracks()` intersects it into a query's projection, which
 * is what makes `tracks()` type-check after that include and fail without it.
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
 * An `albums` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
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

    public string $title { get => $this->title(); }

    public ?float $averageRating { get => $this->averageRating(); }

    public ?string $artwork { get => $this->artwork(); }

    public \DateTimeImmutable $releasedAt { get => $this->releasedAt(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `title`
     */
    public function title(): string
    {
        return $this->_string('title');
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
    public function releasedAt(): \DateTimeImmutable
    {
        return $this->_dateTime('releasedAt');
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
     * A nested object attribute, narrowed member by member: a member of the wrong type is dropped
     * rather than trusted.
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

        if (\is_string($label)) {
            $shape['label'] = $label;
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
     * Whether the `artist` linkage points at anything. Never throws.
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
     * Whether the `tracks` linkage points at anything. Never throws.
     */
    public function hasTracks(): bool
    {
        return !$this->_relationship('tracks', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
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
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `albums` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type AlbumReleaseInfoShape array{label?: string, catalogueNumber?: string}
 * @phpstan-type AlbumStatusInput AlbumStatus|'released'|'upcoming'|'withdrawn'
 * @phpstan-type AlbumDateInput \DateTimeImmutable|string
 * @phpstan-type AlbumArtistInput Artist|Identifier|null
 * @phpstan-type AlbumTracksInput list<Identifier|Track>
 * @phpstan-type AlbumCreateShape array{title: string, releasedAt?: AlbumDateInput, explicit?: bool, status?: AlbumStatusInput, availableFrom?: AlbumDateInput|null, availableUntil?: AlbumDateInput|null, releaseInfo?: AlbumReleaseInfoShape|null, artist?: AlbumArtistInput, tracks?: AlbumTracksInput}
 * @phpstan-type AlbumUpdateShape array{title?: string, releasedAt?: AlbumDateInput, explicit?: bool, status?: AlbumStatusInput, availableFrom?: AlbumDateInput|null, availableUntil?: AlbumDateInput|null, releaseInfo?: AlbumReleaseInfoShape|null, artist?: AlbumArtistInput, tracks?: AlbumTracksInput}
 *
 * @generated from `components.schemas.AlbumsResource`
 */
final class Album extends AlbumBase implements AlbumHasArtist, AlbumHasTracks
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
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
     * Members a create document requires. Read-only attributes (`averageRating`, `artwork`) are in
     * no write shape.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['title'];

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
     * A resource identifier for an `albums` resource that is not in hand, for a relationship write.
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
     * @return AlbumProjection<AlbumBase>
     */
    public static function projection(): AlbumProjection
    {
        return AlbumProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
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
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
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
 * The canonical create input for `albums`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type AlbumCreateShape from Album
 * @phpstan-import-type AlbumReleaseInfoShape from Album
 *
 * @generated from `POST /albums`
 */
final class AlbumCreate
{
    /**
     * @param AlbumReleaseInfoShape|Missing|null $releaseInfo
     * @param Artist|Identifier|Missing|null $artist
     * @param list<Identifier|Track>|Missing $tracks
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

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
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
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => AlbumBase::TYPE];

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
 * The fluent create input for `albums`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /albums`
 */
final class AlbumCreateBuilder
{
    use Conditionable;

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
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): AlbumCreate
    {
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
 * The canonical update input for `albums`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type AlbumReleaseInfoShape from Album
 * @phpstan-import-type AlbumUpdateShape from Album
 *
 * @generated from `PATCH /albums/{id}`
 */
final class AlbumUpdate
{
    use ImmutableConditionable;

    /**
     * @param AlbumReleaseInfoShape|Missing|null $releaseInfo
     * @param Artist|Identifier|Missing|null $artist
     * @param list<Identifier|Track>|Missing $tracks
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
     * @param AlbumReleaseInfoShape|null $releaseInfo
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

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
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
     * The `PATCH /albums/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
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
