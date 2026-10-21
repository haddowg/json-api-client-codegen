<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;

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
 * @generated from `components.schemas.CatalogExportsResource`
 */
final class CatalogExport extends CatalogExportBase
{
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
