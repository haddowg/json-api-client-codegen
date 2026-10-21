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
 * The `parent` relation of a `products` resource, hydrated.
 *
 * A marker carries no implementation. `withParent()` intersects it into a query's projection, which
 * is what makes `parent()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.ProductsResource.properties.relationships.parent`
 */
interface ProductHasParent
{
    /**
     * The related `products` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `parent`
     */
    public function parent(): ?Product;

    public ?Product $parent { get; }
}

/**
 * A `products` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.ProductsResource`
 */
abstract class ProductBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'products';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $name { get => $this->name(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        return $this->_string('name');
    }

    /**
     * The `parent` linkage, without hydrating anything. Never throws.
     */
    public function parentRef(): ?Identifier
    {
        return $this->_relationship('parent', true)->identifier();
    }

    /**
     * Whether the `parent` linkage points at anything. Never throws.
     */
    public function hasParent(): bool
    {
        return !$this->_relationship('parent', true)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'parent' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, true);
    }
}

/**
 * A `products` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `products` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @generated from `components.schemas.ProductsResource`
 */
final class Product extends ProductBase implements ProductHasParent
{
    public ?Product $parent { get => $this->parent(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `products` one
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
     * A resource identifier for a `products` resource that is not in hand, for a relationship
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
     * @return ProductProjection<ProductBase>
     */
    public static function projection(): ProductProjection
    {
        return ProductProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param ProductCreateShape|null $input
     *
     * @return ($input is null ? ProductCreateBuilder : ProductCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `products` does not have
     */
    public static function create(?array $input = null): ProductCreateBuilder|ProductCreate
    {
        return $input === null ? new ProductCreateBuilder() : ProductCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param ProductUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `products` does not have
     */
    public static function update(?array $input = null): ProductUpdate
    {
        return $input === null ? new ProductUpdate() : ProductUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `parent`
     */
    public function parent(): ?Product
    {
        $raw = $this->_includedOne('parent', 'parentRef');

        return $raw === null ? null : Product::_hydrate($raw, $this->_child('parent'));
    }
}
