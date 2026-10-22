<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
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
use haddowg\JsonApiClient\Exceptions\UnknownSortException;
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
 * The `album` relation of a `releases` resource, hydrated.
 *
 * A marker carries no implementation. `withAlbum()` intersects it into a query's projection, which
 * is what makes `album()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.ReleasesResource.properties.relationships.album`
 */
interface ReleaseHasAlbum
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
 * The projection surface of `releases`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see ReleaseProjection} and {@see ReleaseQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `releases` read operations
 */
trait ReleaseProjecting
{
    /**
     * Include the `album` relation, narrowing the projection so `$release->album` type-checks.
     *
     * @return static<TProj&ReleaseHasAlbum>
     */
    public function withAlbum(): static
    {
        return $this->including('album');
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
     * Sparse fieldsets, with the `releases` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[releases]` names a member `releases` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[ReleaseBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Release::FIELDS, true)) {
                throw UnknownFieldException::for(ReleaseBase::TYPE, $field, Release::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `releases` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @phpstan-import-type ReleaseFormatShape from Release
 * @phpstan-import-type ReleasePackagingShape from Release
 * @phpstan-import-type ReleaseAvailabilityShape from Release
 * @phpstan-import-type ReleaseDimensionsShape from Release
 *
 * @generated from `components.schemas.ReleasesResource`
 */
abstract class ReleaseBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'releases';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $catalogNumber { get => $this->catalogNumber(); }

    public ReleaseFormatVinyl|ReleaseFormatCd|ReleaseFormatDigital|null $format { get => $this->format(); }

    /** @var ReleasePackagingShape|null */
    public ?array $packaging { get => $this->packaging(); }

    /** @var ReleaseAvailabilityShape|null */
    public ?array $availability { get => $this->availability(); }

    /** @var ReleaseDimensionsShape|null */
    public ?array $dimensions { get => $this->dimensions(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `catalogNumber`
     */
    public function catalogNumber(): string
    {
        return $this->_string('catalogNumber');
    }

    /**
     * A tagged union, one class per branch, selected by `medium`. Narrow it with `instanceof` or
     * `match`.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `format`
     */
    public function format(): ReleaseFormatVinyl|ReleaseFormatCd|ReleaseFormatDigital|null
    {
        $raw = $this->_nullableMap('format');

        if ($raw === null) {
            return null;
        }

        return match ($raw['medium'] ?? null) {
            ReleaseFormatVinyl::MEDIUM => ReleaseFormatVinyl::_from($raw),
            ReleaseFormatCd::MEDIUM => ReleaseFormatCd::_from($raw),
            ReleaseFormatDigital::MEDIUM => ReleaseFormatDigital::_from($raw),
            default => throw MalformedDocument::unexpectedMember('The "format" attribute of a "releases" resource', 'medium', 'one of "vinyl", "cd", "digital"'),
        };
    }

    /**
     * A nested object attribute, narrowed member by member: a member of the wrong type is dropped
     * rather than trusted.
     *
     * @return ReleasePackagingShape|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `packaging`
     */
    public function packaging(): ?array
    {
        $raw = $this->_nullableMap('packaging');

        if ($raw === null) {
            return null;
        }

        $shape = [];
        $material = $raw['material'] ?? null;
        $gatefold = $raw['gatefold'] ?? null;

        if (\is_string($material)) {
            $shape['material'] = $material;
        }

        if (\is_bool($gatefold)) {
            $shape['gatefold'] = $gatefold;
        }

        return $shape;
    }

    /**
     * A nested object attribute, narrowed member by member: a member of the wrong type is dropped
     * rather than trusted.
     *
     * @return ReleaseAvailabilityShape|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `availability`
     */
    public function availability(): ?array
    {
        $raw = $this->_nullableMap('availability');

        if ($raw === null) {
            return null;
        }

        $shape = [];
        $worldwide = $raw['worldwide'] ?? null;
        $regions = $raw['regions'] ?? null;

        if (\is_bool($worldwide)) {
            $shape['worldwide'] = $worldwide;
        }

        if (\is_array($regions) && \array_is_list($regions)) {
            $regionsList = [];

            foreach ($regions as $regionsItem) {
                if (\is_string($regionsItem)) {
                    $regionsList[] = $regionsItem;
                }
            }

            $shape['regions'] = $regionsList;
        }

        return $shape;
    }

    /**
     * A nested object attribute, narrowed member by member: an optional member of the wrong type is
     * dropped rather than trusted, and a required one throws.
     *
     * @return ReleaseDimensionsShape|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `dimensions`
     */
    public function dimensions(): ?array
    {
        $raw = $this->_nullableMap('dimensions');

        if ($raw === null) {
            return null;
        }

        $shape = [];
        $widthMm = $raw['widthMm'] ?? null;
        $heightMm = $raw['heightMm'] ?? null;
        $depthMm = $raw['depthMm'] ?? null;

        if (!(\is_int($widthMm) || \is_float($widthMm))) {
            throw MalformedDocument::unexpectedMember('The "dimensions" attribute of a "releases" resource', 'widthMm', 'a number');
        }

        $shape['widthMm'] = (float) $widthMm;

        if (!(\is_int($heightMm) || \is_float($heightMm))) {
            throw MalformedDocument::unexpectedMember('The "dimensions" attribute of a "releases" resource', 'heightMm', 'a number');
        }

        $shape['heightMm'] = (float) $heightMm;

        if (\is_int($depthMm) || \is_float($depthMm)) {
            $shape['depthMm'] = (float) $depthMm;
        }

        return $shape;
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
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'album' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, true);
    }
}

/**
 * A `releases` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `releases` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @phpstan-type ReleaseFormatShape array{rpm?: int, coloured?: bool, medium: 'vinyl'}|array{discs?: int, medium: 'cd'}|array{codec?: string, bitrateKbps?: int, medium: 'digital'}
 * @phpstan-type ReleasePackagingShape array{material?: string, gatefold?: bool}
 * @phpstan-type ReleaseAvailabilityShape array{worldwide?: bool, regions?: list<string>}
 * @phpstan-type ReleaseDimensionsShape array{widthMm: float, heightMm: float, depthMm?: float}
 * @phpstan-type ReleaseAlbumInput Album|Identifier|null
 * @phpstan-type ReleaseCreateShape array{catalogNumber: string, format?: array{rpm: int, coloured?: bool, medium: string}|array{discs: int, medium: string}|array{codec: string, bitrateKbps?: int, medium: string}|null, packaging?: array{material: string, gatefold?: bool}|null, availability?: array{worldwide?: bool, regions?: list<string>}|null, dimensions?: array{widthMm: float, heightMm: float, depthMm?: float}|null, album?: ReleaseAlbumInput}
 * @phpstan-type ReleaseUpdateShape array{catalogNumber?: string, format?: array{rpm?: int, coloured?: bool, medium?: string}|array{discs?: int, medium?: string}|array{codec?: string, bitrateKbps?: int, medium?: string}|null, packaging?: ReleasePackagingShape|null, availability?: array{worldwide?: bool, regions?: list<string>}|null, dimensions?: array{widthMm: float, heightMm: float, depthMm?: float}|null, album?: ReleaseAlbumInput}
 * @phpstan-type ReleaseSortToken '-catalogNumber'|'catalogNumber'
 * @phpstan-type ReleaseIncludePath 'album'|'album.artist'|'album.tracks'
 * @phpstan-type ReleaseFieldToken 'album'|'availability'|'catalogNumber'|'dimensions'|'format'|'packaging'
 *
 * @generated from `components.schemas.ReleasesResource`
 */
final class Release extends ReleaseBase implements ReleaseHasAlbum
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['catalogNumber', 'format', 'packaging', 'availability', 'dimensions', 'album'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['catalogNumber'];

    /**
     * The signed sort tokens `GET /releases` accepts, both directions of each.
     *
     * @var list<string>
     */
    public const array SORT_TOKENS = ['catalogNumber', '-catalogNumber'];

    /**
     * The members `fields[releases]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = [
        'catalogNumber',
        'format',
        'packaging',
        'availability',
        'dimensions',
        'album',
    ];

    public ?Album $album { get => $this->album(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `releases` one
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
     * A resource identifier for a `releases` resource that is not in hand, for a relationship
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
     * @return ReleaseProjection<ReleaseBase>
     */
    public static function projection(): ReleaseProjection
    {
        return ReleaseProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param ReleaseCreateShape|null $input
     *
     * @return ($input is null ? ReleaseCreateBuilder : ReleaseCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `releases` does not have
     */
    public static function create(?array $input = null): ReleaseCreateBuilder|ReleaseCreate
    {
        return $input === null ? new ReleaseCreateBuilder() : ReleaseCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param ReleaseUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `releases` does not have
     */
    public static function update(?array $input = null): ReleaseUpdate
    {
        return $input === null ? new ReleaseUpdate() : ReleaseUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `album`
     */
    public function album(): ?Album
    {
        $raw = $this->_includedOne('album', 'albumRef');

        return $raw === null ? null : Album::_hydrate($raw, $this->_child('album'));
    }
}

/**
 * The `album` relationship of one release. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/releases/{id}/album` and `/releases/{id}/relationships/album`
 */
final class ReleaseAlbumRelation
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
     * @throws NotFound           when no release has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/releases/' . $this->id . '/relationships/album'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "album" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no release has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): ?Album
    {
        $context = $this->fetcher->read(Request::get('/releases/' . $this->id . '/album'));
        $raw = $context->primary();

        return $raw === null ? null : Album::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no release has this id
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
            '/releases/' . $this->id . '/relationships/album',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * The canonical create input for `releases`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type ReleaseCreateShape from Release
 *
 * @generated from `POST /releases`
 */
final class ReleaseCreate
{
    /**
     * @param array{rpm: int, coloured?: bool, medium: string}|array{discs: int, medium: string}|array{codec: string, bitrateKbps?: int, medium: string}|Missing|null $format
     * @param array{material: string, gatefold?: bool}|Missing|null $packaging
     * @param array{worldwide?: bool, regions?: list<string>}|Missing|null $availability
     * @param array{widthMm: float, heightMm: float, depthMm?: float}|Missing|null $dimensions
     * @param Album|Identifier|Missing|null $album
     */
    public function __construct(
        public readonly string $catalogNumber,
        public readonly array|Missing|null $format = Missing::Value,
        public readonly array|Missing|null $packaging = Missing::Value,
        public readonly array|Missing|null $availability = Missing::Value,
        public readonly array|Missing|null $dimensions = Missing::Value,
        public readonly Album|Identifier|Missing|null $album = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param ReleaseCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `releases`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Release::WRITABLE, true)) {
                throw UnknownAttributeException::for(ReleaseBase::TYPE, (string) $member, Release::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            catalogNumber: $input['catalogNumber'],
            format: \array_key_exists('format', $input) ? $input['format'] : Missing::Value,
            packaging: \array_key_exists('packaging', $input) ? $input['packaging'] : Missing::Value,
            availability: \array_key_exists('availability', $input) ? $input['availability'] : Missing::Value,
            dimensions: \array_key_exists('dimensions', $input) ? $input['dimensions'] : Missing::Value,
            album: \array_key_exists('album', $input) ? $input['album'] : Missing::Value,
        );
    }

    /**
     * The `POST /releases` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => ReleaseBase::TYPE];

        $attributes = ['catalogNumber' => $this->catalogNumber];

        if (!$this->format instanceof Missing) {
            $attributes['format'] = $this->format;
        }

        if (!$this->packaging instanceof Missing) {
            $attributes['packaging'] = $this->packaging;
        }

        if (!$this->availability instanceof Missing) {
            $attributes['availability'] = $this->availability;
        }

        if (!$this->dimensions instanceof Missing) {
            $attributes['dimensions'] = $this->dimensions;
        }

        $data['attributes'] = $attributes;

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
 * The fluent create input for `releases`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /releases`
 */
final class ReleaseCreateBuilder
{
    use Conditionable;

    private string|Missing $catalogNumber = Missing::Value;

    /**
     * @var array{rpm: int, coloured?: bool, medium: string}|array{discs: int, medium: string}|array{codec: string, bitrateKbps?: int, medium: string}|Missing|null
     */
    private array|Missing|null $format = Missing::Value;

    /**
     * @var array{material: string, gatefold?: bool}|Missing|null
     */
    private array|Missing|null $packaging = Missing::Value;

    /**
     * @var array{worldwide?: bool, regions?: list<string>}|Missing|null
     */
    private array|Missing|null $availability = Missing::Value;

    /**
     * @var array{widthMm: float, heightMm: float, depthMm?: float}|Missing|null
     */
    private array|Missing|null $dimensions = Missing::Value;

    private Album|Identifier|Missing|null $album = Missing::Value;

    public function catalogNumber(string $catalogNumber): self
    {
        $this->catalogNumber = $catalogNumber;

        return $this;
    }

    /**
     * @param array{rpm: int, coloured?: bool, medium: string}|array{discs: int, medium: string}|array{codec: string, bitrateKbps?: int, medium: string}|null $format
     */
    public function format(?array $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param array{material: string, gatefold?: bool}|null $packaging
     */
    public function packaging(?array $packaging): self
    {
        $this->packaging = $packaging;

        return $this;
    }

    /**
     * @param array{worldwide?: bool, regions?: list<string>}|null $availability
     */
    public function availability(?array $availability): self
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * @param array{widthMm: float, heightMm: float, depthMm?: float}|null $dimensions
     */
    public function dimensions(?array $dimensions): self
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function album(Album|Identifier|null $album): self
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): ReleaseCreate
    {
        if ($this->catalogNumber instanceof Missing) {
            throw MissingRequiredMemberException::for(ReleaseBase::TYPE, ['catalogNumber']);
        }

        return new ReleaseCreate(
            catalogNumber: $this->catalogNumber,
            format: $this->format,
            packaging: $this->packaging,
            availability: $this->availability,
            dimensions: $this->dimensions,
            album: $this->album,
        );
    }
}

/**
 * The `cd` branch of the `format` attribute of a `releases` resource, selected by `medium`.
 *
 * @generated from `components.schemas.ReleasesResource`, the `cd` branch of `format`
 */
final class ReleaseFormatCd
{
    /**
     * The `medium` value that selects this branch.
     */
    public const string MEDIUM = 'cd';

    public function __construct(
        public readonly ?int $discs = null,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $discs = $raw['discs'] ?? null;

        $discsValue = null;
        if (\is_int($discs)) {
            $discsValue = $discs;
        }

        return new self($discsValue);
    }
}

/**
 * The `digital` branch of the `format` attribute of a `releases` resource, selected by `medium`.
 *
 * @generated from `components.schemas.ReleasesResource`, the `digital` branch of `format`
 */
final class ReleaseFormatDigital
{
    /**
     * The `medium` value that selects this branch.
     */
    public const string MEDIUM = 'digital';

    public function __construct(
        public readonly ?string $codec = null,
        public readonly ?int $bitrateKbps = null,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $codec = $raw['codec'] ?? null;
        $bitrateKbps = $raw['bitrateKbps'] ?? null;

        $codecValue = null;
        if (\is_string($codec)) {
            $codecValue = $codec;
        }

        $bitrateKbpsValue = null;
        if (\is_int($bitrateKbps)) {
            $bitrateKbpsValue = $bitrateKbps;
        }

        return new self($codecValue, $bitrateKbpsValue);
    }
}

/**
 * The `vinyl` branch of the `format` attribute of a `releases` resource, selected by `medium`.
 *
 * @generated from `components.schemas.ReleasesResource`, the `vinyl` branch of `format`
 */
final class ReleaseFormatVinyl
{
    /**
     * The `medium` value that selects this branch.
     */
    public const string MEDIUM = 'vinyl';

    public function __construct(
        public readonly ?int $rpm = null,
        public readonly ?bool $coloured = null,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $rpm = $raw['rpm'] ?? null;
        $coloured = $raw['coloured'] ?? null;

        $rpmValue = null;
        if (\is_int($rpm)) {
            $rpmValue = $rpm;
        }

        $colouredValue = null;
        if (\is_bool($coloured)) {
            $colouredValue = $coloured;
        }

        return new self($rpmValue, $colouredValue);
    }
}

/**
 * One release by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type ReleaseUpdateShape from Release
 *
 * @generated from the `releases` operations on one resource
 */
final class ReleaseHandle
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
     * Fetch the release, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(ReleaseProjection<ReleaseBase>): ReleaseProjection<TProj>)|ReleaseProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ReleaseBase : ReleaseBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no release has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|ReleaseProjection|null $projection = null): ReleaseBase
    {
        $query = ReleaseProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/releases/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The releases response', 'data');

        /** @var ReleaseBase&TProj */
        return $context->hydrate($raw, Release::_hydrate(...));
    }

    /**
     * Patch the release, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param ReleaseUpdate|ReleaseUpdateShape                                                                   $input
     * @param (callable(ReleaseProjection<ReleaseBase>): ReleaseProjection<TProj>)|ReleaseProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ReleaseBase : ReleaseBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no release has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function update(
        ReleaseUpdate|array $input,
        callable|ReleaseProjection|null $projection = null,
    ): ReleaseBase {
        $document = match (true) {
            $input instanceof ReleaseUpdate => $input,
            default => ReleaseUpdate::from($input),
        };

        $query = ReleaseProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/releases/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var ReleaseBase&TProj */
        return $context->hydrate($raw, Release::_hydrate(...));
    }

    /**
     * Delete the release.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no release has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/releases/' . $this->id));
    }

    /**
     * The `album` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function album(): ReleaseAlbumRelation
    {
        return new ReleaseAlbumRelation($this->fetcher, $this->id);
    }
}

/**
 * What a response carrying `releases` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /releases/{id}`, `POST /releases`, `PATCH /releases/{id}`
 */
final class ReleaseProjection extends Projection
{
    /** @use ReleaseProjecting<TProj> */
    use ReleaseProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<ReleaseBase>
     */
    public static function make(): self
    {
        /** @var self<ReleaseBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<ReleaseBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /releases`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type ReleaseIncludePath from Release
 * @phpstan-import-type ReleaseSortToken from Release
 *
 * @generated from `GET /releases`
 */
final class ReleaseQuery extends Query
{
    /** @use ReleaseProjecting<TProj> */
    use ReleaseProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Sort by the endpoint's own tokens, in precedence order. Argument order is precedence, so
     * nothing is translated between the call and the wire.
     *
     * @param ReleaseSortToken ...$tokens
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
            if (!\in_array($token, Release::SORT_TOKENS, true)) {
                throw UnknownSortException::for($token, Release::SORT_TOKENS);
            }
        }

        return $this->sorting($tokens);
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
     * Paginator parameters assembled at runtime. Public here because `GET /releases` is paginated;
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
     * The first release the read matches, fetching a single-resource page rather than a whole one.
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
        return Request::get($query->appendTo('/releases'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $releases = [];

        foreach ($context->primaryList() as $raw) {
            $releases[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $releases,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Release`, but `TProj`
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
        return $context->hydrate($raw, Release::_hydrate(...));
    }
}

/**
 * The canonical update input for `releases`: every member optional, which is what a JSON:API
 * `PATCH` is. The id is not a member; it comes from the handle the update is called on, so it
 * cannot disagree with the URL.
 *
 * @phpstan-import-type ReleasePackagingShape from Release
 * @phpstan-import-type ReleaseUpdateShape from Release
 *
 * @generated from `PATCH /releases/{id}`
 */
final class ReleaseUpdate
{
    use ImmutableConditionable;

    /**
     * @param array{rpm?: int, coloured?: bool, medium?: string}|array{discs?: int, medium?: string}|array{codec?: string, bitrateKbps?: int, medium?: string}|Missing|null $format
     * @param ReleasePackagingShape|Missing|null $packaging
     * @param array{worldwide?: bool, regions?: list<string>}|Missing|null $availability
     * @param array{widthMm: float, heightMm: float, depthMm?: float}|Missing|null $dimensions
     * @param Album|Identifier|Missing|null $album
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $catalogNumber = Missing::Value,
        public readonly array|Missing|null $format = Missing::Value,
        public readonly array|Missing|null $packaging = Missing::Value,
        public readonly array|Missing|null $availability = Missing::Value,
        public readonly array|Missing|null $dimensions = Missing::Value,
        public readonly Album|Identifier|Missing|null $album = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function catalogNumber(string $catalogNumber): self
    {
        return new self(
            catalogNumber: $catalogNumber,
            format: $this->format,
            packaging: $this->packaging,
            availability: $this->availability,
            dimensions: $this->dimensions,
            album: $this->album,
        );
    }

    /**
     * @param array{rpm?: int, coloured?: bool, medium?: string}|array{discs?: int, medium?: string}|array{codec?: string, bitrateKbps?: int, medium?: string}|null $format
     *
     * @phpstan-pure
     */
    public function format(?array $format): self
    {
        return new self(
            catalogNumber: $this->catalogNumber,
            format: $format,
            packaging: $this->packaging,
            availability: $this->availability,
            dimensions: $this->dimensions,
            album: $this->album,
        );
    }

    /**
     * @param ReleasePackagingShape|null $packaging
     *
     * @phpstan-pure
     */
    public function packaging(?array $packaging): self
    {
        return new self(
            catalogNumber: $this->catalogNumber,
            format: $this->format,
            packaging: $packaging,
            availability: $this->availability,
            dimensions: $this->dimensions,
            album: $this->album,
        );
    }

    /**
     * @param array{worldwide?: bool, regions?: list<string>}|null $availability
     *
     * @phpstan-pure
     */
    public function availability(?array $availability): self
    {
        return new self(
            catalogNumber: $this->catalogNumber,
            format: $this->format,
            packaging: $this->packaging,
            availability: $availability,
            dimensions: $this->dimensions,
            album: $this->album,
        );
    }

    /**
     * @param array{widthMm: float, heightMm: float, depthMm?: float}|null $dimensions
     *
     * @phpstan-pure
     */
    public function dimensions(?array $dimensions): self
    {
        return new self(
            catalogNumber: $this->catalogNumber,
            format: $this->format,
            packaging: $this->packaging,
            availability: $this->availability,
            dimensions: $dimensions,
            album: $this->album,
        );
    }

    /**
     * @phpstan-pure
     */
    public function album(Album|Identifier|null $album): self
    {
        return new self(
            catalogNumber: $this->catalogNumber,
            format: $this->format,
            packaging: $this->packaging,
            availability: $this->availability,
            dimensions: $this->dimensions,
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
     * @param ReleaseUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `releases`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Release::WRITABLE, true)) {
                throw UnknownAttributeException::for(ReleaseBase::TYPE, (string) $member, Release::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            catalogNumber: \array_key_exists('catalogNumber', $input) ? $input['catalogNumber'] : Missing::Value,
            format: \array_key_exists('format', $input) ? $input['format'] : Missing::Value,
            packaging: \array_key_exists('packaging', $input) ? $input['packaging'] : Missing::Value,
            availability: \array_key_exists('availability', $input) ? $input['availability'] : Missing::Value,
            dimensions: \array_key_exists('dimensions', $input) ? $input['dimensions'] : Missing::Value,
            album: \array_key_exists('album', $input) ? $input['album'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /releases/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => ReleaseBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->catalogNumber instanceof Missing) {
            $attributes['catalogNumber'] = $this->catalogNumber;
        }

        if (!$this->format instanceof Missing) {
            $attributes['format'] = $this->format;
        }

        if (!$this->packaging instanceof Missing) {
            $attributes['packaging'] = $this->packaging;
        }

        if (!$this->availability instanceof Missing) {
            $attributes['availability'] = $this->availability;
        }

        if (!$this->dimensions instanceof Missing) {
            $attributes['dimensions'] = $this->dimensions;
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
 * The `releases` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type ReleaseCreateShape from Release
 *
 * @generated from the `releases` operations
 */
final class Releases
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the releases collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return ReleaseQuery<ReleaseBase>
     */
    public function query(): ReleaseQuery
    {
        /** @var ReleaseQuery<ReleaseBase> */
        return new ReleaseQuery($this->fetcher);
    }

    /**
     * A handle on one release by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): ReleaseHandle
    {
        return new ReleaseHandle($this->fetcher, $id);
    }

    /**
     * Create a release, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param ReleaseCreate|ReleaseCreateBuilder|ReleaseCreateShape                                              $input
     * @param (callable(ReleaseProjection<ReleaseBase>): ReleaseProjection<TProj>)|ReleaseProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ReleaseBase : ReleaseBase&TProj)
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
        ReleaseCreate|ReleaseCreateBuilder|array $input,
        callable|ReleaseProjection|null $projection = null,
    ): ReleaseBase {
        $document = match (true) {
            $input instanceof ReleaseCreate => $input,
            $input instanceof ReleaseCreateBuilder => $input->build(),
            default => ReleaseCreate::from($input),
        };

        $query = ReleaseProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/releases'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var ReleaseBase&TProj */
        return $context->hydrate($raw, Release::_hydrate(...));
    }
}
