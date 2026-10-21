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
 * @phpstan-type TrackAlbumInput Album|Identifier|null
 * @phpstan-type TrackPlaylistsInput list<Identifier|Playlist>
 * @phpstan-type TrackCreateShape array{title: string, trackNumber?: int, durationSeconds?: int, explicit?: bool, genres?: list<string>, previewOffset?: string|null, album?: TrackAlbumInput, playlists?: TrackPlaylistsInput}
 * @phpstan-type TrackUpdateShape array{title?: string, trackNumber?: int, durationSeconds?: int, explicit?: bool, genres?: list<string>, previewOffset?: string|null, album?: TrackAlbumInput}
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
