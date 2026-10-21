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
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\ImmutableConditionable;
use haddowg\JsonApiClient\Support\Missing;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * The projection surface of `genres`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see GenreProjection} and {@see GenreQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `genres` read operations
 */
trait GenreProjecting
{
    /**
     * Sparse fieldsets, with the `genres` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[genres]` names a member `genres` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[GenreBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Genre::FIELDS, true)) {
                throw UnknownFieldException::for(GenreBase::TYPE, $field, Genre::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `genres` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.GenresResource`
 */
abstract class GenreBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'genres';

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
}

/**
 * A `genres` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `genres` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type GenreCreateShape array{id: string, name: string}
 * @phpstan-type GenreUpdateShape array{name?: string}
 * @phpstan-type GenreFieldToken 'name'
 *
 * @generated from `components.schemas.GenresResource`
 */
final class Genre extends GenreBase
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['name'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['name'];

    /**
     * The members `fields[genres]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['name'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `genres` one
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
     * A resource identifier for a `genres` resource that is not in hand, for a relationship write.
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
     * @return GenreProjection<GenreBase>
     */
    public static function projection(): GenreProjection
    {
        return GenreProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param GenreCreateShape|null $input
     *
     * @return ($input is null ? GenreCreateBuilder : GenreCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `genres` does not have
     */
    public static function create(?array $input = null): GenreCreateBuilder|GenreCreate
    {
        return $input === null ? new GenreCreateBuilder() : GenreCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param GenreUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `genres` does not have
     */
    public static function update(?array $input = null): GenreUpdate
    {
        return $input === null ? new GenreUpdate() : GenreUpdate::from($input);
    }
}

/**
 * The canonical create input for `genres`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type GenreCreateShape from Genre
 *
 * @generated from `POST /genres`
 */
final class GenreCreate
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param GenreCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `genres`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if ($member !== 'id' && !\in_array($member, Genre::WRITABLE, true)) {
                throw UnknownAttributeException::for(GenreBase::TYPE, (string) $member, Genre::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            id: $input['id'],
            name: $input['name'],
        );
    }

    /**
     * The `POST /genres` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => GenreBase::TYPE, 'id' => $this->id];

        $attributes = ['name' => $this->name];

        $data['attributes'] = $attributes;

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `genres`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /genres`
 */
final class GenreCreateBuilder
{
    use Conditionable;

    private string|Missing $id = Missing::Value;

    private string|Missing $name = Missing::Value;

    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): GenreCreate
    {
        if ($this->id instanceof Missing) {
            throw MissingRequiredMemberException::for(GenreBase::TYPE, ['id']);
        }

        if ($this->name instanceof Missing) {
            throw MissingRequiredMemberException::for(GenreBase::TYPE, ['name']);
        }

        return new GenreCreate(
            id: $this->id,
            name: $this->name,
        );
    }
}

/**
 * What a response carrying `genres` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /genres/{id}`, `POST /genres`, `PATCH /genres/{id}`
 */
final class GenreProjection extends Projection
{
    /** @use GenreProjecting<TProj> */
    use GenreProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<GenreBase>
     */
    public static function make(): self
    {
        /** @var self<GenreBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<GenreBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /genres`: the projection, plus everything only a read can carry.
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
 * @generated from `GET /genres`
 */
final class GenreQuery extends Query
{
    /** @use GenreProjecting<TProj> */
    use GenreProjecting;
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
     * The first genre the read matches, fetching a single-resource page rather than a whole one.
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
        return Request::get($query->appendTo('/genres'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $genres = [];

        foreach ($context->primaryList() as $raw) {
            $genres[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $genres,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Genre`, but `TProj`
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
        return $context->hydrate($raw, Genre::_hydrate(...));
    }
}

/**
 * The canonical update input for `genres`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type GenreUpdateShape from Genre
 *
 * @generated from `PATCH /genres/{id}`
 */
final class GenreUpdate
{
    use ImmutableConditionable;

    /**
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $name = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function name(string $name): self
    {
        return new self(
            name: $name,
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
     * @param GenreUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `genres`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Genre::WRITABLE, true)) {
                throw UnknownAttributeException::for(GenreBase::TYPE, (string) $member, Genre::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: \array_key_exists('name', $input) ? $input['name'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /genres/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => GenreBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->name instanceof Missing) {
            $attributes['name'] = $this->name;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        return ['data' => $data];
    }
}
