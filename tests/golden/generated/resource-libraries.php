<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
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
 * The `items` relation of a `libraries` resource, hydrated.
 *
 * A marker carries no implementation. `withItems()` intersects it into a query's projection, which
 * is what makes `items()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.LibrariesResource.properties.relationships.items`
 */
interface LibraryHasItems
{
    /**
     * @return ResourceCollection<Track|Album|Artist>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `items`
     */
    public function items(): ResourceCollection;

    /** @var ResourceCollection<Track|Album|Artist> */
    public ResourceCollection $items { get; }
}

/**
 * The `owner` relation of a `libraries` resource, hydrated.
 *
 * A marker carries no implementation. `withOwner()` intersects it into a query's projection, which
 * is what makes `owner()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.LibrariesResource.properties.relationships.owner`
 */
interface LibraryHasOwner
{
    /**
     * The related `public-profiles` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `owner`
     */
    public function owner(): ?PublicProfile;

    public ?PublicProfile $owner { get; }
}

/**
 * The projection surface of `libraries`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see LibraryProjection} and {@see LibraryQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `libraries` read operations
 */
trait LibraryProjecting
{
    /**
     * Include the `owner` relation, narrowing the projection so `$library->owner` type-checks.
     *
     * @return static<TProj&LibraryHasOwner>
     */
    public function withOwner(): static
    {
        return $this->including('owner');
    }

    /**
     * Include the `items` relation, narrowing the projection so `$library->items` type-checks.
     *
     * @return static<TProj&LibraryHasItems>
     */
    public function withItems(): static
    {
        return $this->including('items');
    }

    /**
     * Sparse fieldsets, with the `libraries` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[libraries]` names a member `libraries` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[LibraryBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Library::FIELDS, true)) {
                throw UnknownFieldException::for(LibraryBase::TYPE, $field, Library::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `libraries` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * @generated from `components.schemas.LibrariesResource`
 */
abstract class LibraryBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'libraries';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

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
     * The `owner` linkage, without hydrating anything. Never throws.
     */
    public function ownerRef(): ?Identifier
    {
        return $this->_relationship('owner', true)->identifier();
    }

    /**
     * Whether the `owner` linkage points at anything. Never throws.
     */
    public function hasOwner(): bool
    {
        return !$this->_relationship('owner', true)->isEmpty();
    }

    /**
     * The `items` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function itemsRef(): array
    {
        return $this->_relationship('items', false)->identifiers();
    }

    /**
     * Whether the `items` linkage points at anything. Never throws.
     */
    public function hasItems(): bool
    {
        return !$this->_relationship('items', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'owner'|'items' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, $name === 'owner');
    }
}

/**
 * A `libraries` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `libraries` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @phpstan-type LibraryOwnerInput Identifier|PublicProfile|null
 * @phpstan-type LibraryItemsInput list<Album|Artist|Identifier|Track>
 * @phpstan-type LibraryCreateShape array{owner?: LibraryOwnerInput, items?: LibraryItemsInput}
 * @phpstan-type LibraryUpdateShape array{owner?: LibraryOwnerInput, items?: LibraryItemsInput}
 * @phpstan-type LibraryIncludePath 'items'|'owner'
 * @phpstan-type LibraryFieldToken 'items'|'owner'
 *
 * @generated from `components.schemas.LibrariesResource`
 */
final class Library extends LibraryBase implements LibraryHasOwner, LibraryHasItems
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['owner', 'items'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = [];

    /**
     * The members `fields[libraries]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['owner', 'items'];

    public ?PublicProfile $owner { get => $this->owner(); }

    /** @var ResourceCollection<Track|Album|Artist> */
    public ResourceCollection $items { get => $this->items(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `libraries` one
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
     * A resource identifier for a `libraries` resource that is not in hand, for a relationship
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
     * @return LibraryProjection<LibraryBase>
     */
    public static function projection(): LibraryProjection
    {
        return LibraryProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param LibraryCreateShape|null $input
     *
     * @return ($input is null ? LibraryCreateBuilder : LibraryCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `libraries` does not have
     */
    public static function create(?array $input = null): LibraryCreateBuilder|LibraryCreate
    {
        return $input === null ? new LibraryCreateBuilder() : LibraryCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param LibraryUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `libraries` does not have
     */
    public static function update(?array $input = null): LibraryUpdate
    {
        return $input === null ? new LibraryUpdate() : LibraryUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `owner`
     */
    public function owner(): ?PublicProfile
    {
        $raw = $this->_includedOne('owner', 'ownerRef');

        return $raw === null ? null : PublicProfile::_hydrate($raw, $this->_child('owner'));
    }

    /**
     * @return ResourceCollection<Track|Album|Artist>
     *
     * @throws RelationNotIncludedException when the read did not include `items`
     */
    public function items(): ResourceCollection
    {
        $context = $this->_child('items');
        $items = [];

        foreach ($this->_includedMany('items', 'itemsRef') as $raw) {
            $items[] = match ($raw['type'] ?? null) {
                Track::TYPE => Track::_hydrate($raw, $context),
                Album::TYPE => Album::_hydrate($raw, $context),
                Artist::TYPE => Artist::_hydrate($raw, $context),
                default => throw MalformedDocument::unexpectedMember('The "items" relationship', 'type', 'one of "tracks", "albums", "artists"'),
            };
        }

        return new ResourceCollection($items);
    }
}

/**
 * The canonical create input for `libraries`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type LibraryCreateShape from Library
 *
 * @generated from `POST /libraries`
 */
final class LibraryCreate
{
    /**
     * @param Identifier|PublicProfile|Missing|null $owner
     * @param list<Album|Artist|Identifier|Track>|Missing $items
     */
    public function __construct(
        public readonly Identifier|PublicProfile|Missing|null $owner = Missing::Value,
        public readonly array|Missing $items = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param LibraryCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `libraries`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Library::WRITABLE, true)) {
                throw UnknownAttributeException::for(LibraryBase::TYPE, (string) $member, Library::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            owner: \array_key_exists('owner', $input) ? $input['owner'] : Missing::Value,
            items: \array_key_exists('items', $input) ? $input['items'] : Missing::Value,
        );
    }

    /**
     * The `POST /libraries` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => LibraryBase::TYPE];

        $relationships = [];

        if (!$this->owner instanceof Missing) {
            $relationships['owner'] = ['data' => Coerce::linkage($this->owner)];
        }

        if (!$this->items instanceof Missing) {
            $relationships['items'] = ['data' => Coerce::linkageList($this->items)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `libraries`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /libraries`
 */
final class LibraryCreateBuilder
{
    use Conditionable;

    private Identifier|PublicProfile|Missing|null $owner = Missing::Value;

    /**
     * @var list<Album|Artist|Identifier|Track>|Missing
     */
    private array|Missing $items = Missing::Value;

    public function owner(Identifier|PublicProfile|null $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param list<Album|Artist|Identifier|Track> $items
     */
    public function items(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     */
    public function build(): LibraryCreate
    {
        return new LibraryCreate(
            owner: $this->owner,
            items: $this->items,
        );
    }
}

/**
 * What a response carrying `libraries` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /libraries/{id}`, `POST /libraries`, `PATCH /libraries/{id}`
 */
final class LibraryProjection extends Projection
{
    /** @use LibraryProjecting<TProj> */
    use LibraryProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<LibraryBase>
     */
    public static function make(): self
    {
        /** @var self<LibraryBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<LibraryBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /libraries`: the projection, plus everything only a read can carry.
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
 * @phpstan-import-type LibraryIncludePath from Library
 *
 * @generated from `GET /libraries`
 */
final class LibraryQuery extends Query
{
    /** @use LibraryProjecting<TProj> */
    use LibraryProjecting;
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
     * The first library the read matches, fetching a single-resource page rather than a whole one.
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
        return Request::get($query->appendTo('/libraries'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $libraries = [];

        foreach ($context->primaryList() as $raw) {
            $libraries[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $libraries,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Library`, but `TProj`
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
        return $context->hydrate($raw, Library::_hydrate(...));
    }
}

/**
 * The canonical update input for `libraries`: every member optional, which is what a JSON:API
 * `PATCH` is. The id is not a member; it comes from the handle the update is called on, so it
 * cannot disagree with the URL.
 *
 * @phpstan-import-type LibraryUpdateShape from Library
 *
 * @generated from `PATCH /libraries/{id}`
 */
final class LibraryUpdate
{
    use ImmutableConditionable;

    /**
     * @param Identifier|PublicProfile|Missing|null $owner
     * @param list<Album|Artist|Identifier|Track>|Missing $items
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly Identifier|PublicProfile|Missing|null $owner = Missing::Value,
        public readonly array|Missing $items = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function owner(Identifier|PublicProfile|null $owner): self
    {
        return new self(
            owner: $owner,
            items: $this->items,
        );
    }

    /**
     * @param list<Album|Artist|Identifier|Track> $items
     *
     * @phpstan-pure
     */
    public function items(array $items): self
    {
        return new self(
            owner: $this->owner,
            items: $items,
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
     * @param LibraryUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `libraries`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Library::WRITABLE, true)) {
                throw UnknownAttributeException::for(LibraryBase::TYPE, (string) $member, Library::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            owner: \array_key_exists('owner', $input) ? $input['owner'] : Missing::Value,
            items: \array_key_exists('items', $input) ? $input['items'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /libraries/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => LibraryBase::TYPE, 'id' => $id];

        $relationships = [];

        if (!$this->owner instanceof Missing) {
            $relationships['owner'] = ['data' => Coerce::linkage($this->owner)];
        }

        if (!$this->items instanceof Missing) {
            $relationships['items'] = ['data' => Coerce::linkageList($this->items)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}
