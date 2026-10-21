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
 *
 * @generated from `components.schemas.ReleasesResource`
 */
final class Release extends ReleaseBase implements ReleaseHasAlbum
{
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
