<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
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
 * The projection surface of `products`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see ProductProjection} and {@see ProductQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `products` read operations
 */
trait ProductProjecting
{
    /**
     * Include the `parent` relation, narrowing the projection so `$product->parent` type-checks.
     *
     * @return static<TProj&ProductHasParent>
     */
    public function withParent(): static
    {
        return $this->including('parent');
    }

    /**
     * Sparse fieldsets, with the `products` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[products]` names a member `products` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[ProductBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Product::FIELDS, true)) {
                throw UnknownFieldException::for(ProductBase::TYPE, $field, Product::FIELDS);
            }
        }

        return parent::fields($fields);
    }
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
 * @phpstan-type ProductIncludePath 'parent'
 * @phpstan-type ProductFieldToken 'name'|'parent'
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

    /**
     * The members `fields[products]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['name', 'parent'];

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
 * What a response carrying `products` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /products/{id}`, `POST /products`, `PATCH /products/{id}`
 */
final class ProductProjection extends Projection
{
    /** @use ProductProjecting<TProj> */
    use ProductProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<ProductBase>
     */
    public static function make(): self
    {
        /** @var self<ProductBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<ProductBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /products`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not. Filters come in three doors:
 * a `whereX()` per filter, the typed `filter()` shape, and the loose `filterRaw()`. They are
 * separate methods because a parameter typed as the shape or any array would accept anything. Both
 * array doors still check names at runtime.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type ProductIncludePath from Product
 *
 * @generated from `GET /products`
 */
final class ProductQuery extends Query
{
    /** @use ProductProjecting<TProj> */
    use ProductProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

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
     * The first product the read matches, fetching a single-resource page rather than a whole one.
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
        return Request::get($query->appendTo('/products'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $products = [];

        foreach ($context->primaryList() as $raw) {
            $products[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $products,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Product`, but `TProj`
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
        return $context->hydrate($raw, Product::_hydrate(...));
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
