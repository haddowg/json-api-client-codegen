<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\Missing;

/**
 * A `catalog-exports` resource with no relation hydrated: everything a read is guaranteed to give
 * you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.CatalogExportsResource`
 */
abstract class CatalogExportBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'catalog-exports';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $format { get => $this->format(); }

    public string $status { get => $this->status(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `format`
     */
    public function format(): string
    {
        return $this->_string('format');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `status`
     */
    public function status(): string
    {
        return $this->_string('status');
    }
}

/**
 * A `catalog-exports` resource as it actually arrives: every attribute, and every relation
 * accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `catalog-exports` surface accepts, and the descriptor
 * constants the runtime guards check against. `@phpstan-type` aliases are class-local, so every
 * other class using one imports it from here.
 *
 * @phpstan-type CatalogExportCreateShape array{format: string}
 *
 * @generated from `components.schemas.CatalogExportsResource`
 */
final class CatalogExport extends CatalogExportBase
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['format'];

    /**
     * Members a create document requires. Read-only attributes (`status`) are in no write shape.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['format'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `catalog-exports` one
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
     * A resource identifier for a `catalog-exports` resource that is not in hand, for a
     * relationship write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param CatalogExportCreateShape|null $input
     *
     * @return ($input is null ? CatalogExportCreateBuilder : CatalogExportCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `catalog-exports` does not have
     */
    public static function create(?array $input = null): CatalogExportCreateBuilder|CatalogExportCreate
    {
        return $input === null ? new CatalogExportCreateBuilder() : CatalogExportCreate::from($input);
    }
}

/**
 * The canonical create input for `catalog-exports`, as named arguments. A missing required member
 * is a static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type CatalogExportCreateShape from CatalogExport
 *
 * @generated from `POST /catalog-exports`
 */
final class CatalogExportCreate
{
    public function __construct(
        public readonly string $format,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param CatalogExportCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `catalog-exports`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, CatalogExport::WRITABLE, true)) {
                throw UnknownAttributeException::for(CatalogExportBase::TYPE, (string) $member, CatalogExport::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            format: $input['format'],
        );
    }

    /**
     * The `POST /catalog-exports` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => CatalogExportBase::TYPE];

        $attributes = ['format' => $this->format];

        $data['attributes'] = $attributes;

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `catalog-exports`, for assembly that is conditional. `build()`
 * rejects a missing required member, which this form cannot check statically.
 *
 * @generated from `POST /catalog-exports`
 */
final class CatalogExportCreateBuilder
{
    use Conditionable;

    private string|Missing $format = Missing::Value;

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): CatalogExportCreate
    {
        if ($this->format instanceof Missing) {
            throw MissingRequiredMemberException::for(CatalogExportBase::TYPE, ['format']);
        }

        return new CatalogExportCreate(
            format: $this->format,
        );
    }
}
