<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

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
