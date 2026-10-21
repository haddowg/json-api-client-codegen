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
 * @phpstan-type ProductParentInput Identifier|Product|null
 * @phpstan-type ProductCreateShape array{name: string, parent?: ProductParentInput}
 * @phpstan-type ProductUpdateShape array{name?: string, parent?: ProductParentInput}
 *
 * @generated from `components.schemas.ProductsResource`
 */
final class Product extends ProductBase implements ProductHasParent
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['name', 'parent'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['name'];

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

/**
 * The canonical create input for `products`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type ProductCreateShape from Product
 *
 * @generated from `POST /products`
 */
final class ProductCreate
{
    /**
     * @param Identifier|Product|Missing|null $parent
     */
    public function __construct(
        public readonly string $name,
        public readonly Identifier|Product|Missing|null $parent = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param ProductCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `products`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Product::WRITABLE, true)) {
                throw UnknownAttributeException::for(ProductBase::TYPE, (string) $member, Product::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: $input['name'],
            parent: \array_key_exists('parent', $input) ? $input['parent'] : Missing::Value,
        );
    }

    /**
     * The `POST /products` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => ProductBase::TYPE];

        $attributes = ['name' => $this->name];

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->parent instanceof Missing) {
            $relationships['parent'] = ['data' => Coerce::linkage($this->parent)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `products`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /products`
 */
final class ProductCreateBuilder
{
    use Conditionable;

    private string|Missing $name = Missing::Value;

    private Identifier|Product|Missing|null $parent = Missing::Value;

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function parent(Identifier|Product|null $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): ProductCreate
    {
        if ($this->name instanceof Missing) {
            throw MissingRequiredMemberException::for(ProductBase::TYPE, ['name']);
        }

        return new ProductCreate(
            name: $this->name,
            parent: $this->parent,
        );
    }
}

/**
 * The canonical update input for `products`: every member optional, which is what a JSON:API
 * `PATCH` is. The id is not a member; it comes from the handle the update is called on, so it
 * cannot disagree with the URL.
 *
 * @phpstan-import-type ProductUpdateShape from Product
 *
 * @generated from `PATCH /products/{id}`
 */
final class ProductUpdate
{
    use ImmutableConditionable;

    /**
     * @param Identifier|Product|Missing|null $parent
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $name = Missing::Value,
        public readonly Identifier|Product|Missing|null $parent = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function name(string $name): self
    {
        return new self(
            name: $name,
            parent: $this->parent,
        );
    }

    /**
     * @phpstan-pure
     */
    public function parent(Identifier|Product|null $parent): self
    {
        return new self(
            name: $this->name,
            parent: $parent,
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
     * @param ProductUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `products`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Product::WRITABLE, true)) {
                throw UnknownAttributeException::for(ProductBase::TYPE, (string) $member, Product::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: \array_key_exists('name', $input) ? $input['name'] : Missing::Value,
            parent: \array_key_exists('parent', $input) ? $input['parent'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /products/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => ProductBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->name instanceof Missing) {
            $attributes['name'] = $this->name;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->parent instanceof Missing) {
            $relationships['parent'] = ['data' => Coerce::linkage($this->parent)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}
