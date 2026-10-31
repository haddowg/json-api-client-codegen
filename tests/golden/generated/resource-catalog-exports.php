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
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Http\Response;
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
use haddowg\JsonApiClient\Support\Missing;
use haddowg\JsonApiClient\Support\QueryConditionable;
use haddowg\JsonApiClient\Writes\CreateInput;

/**
 * The projection surface of `catalog-exports`: the `withX()` include methods and the checked
 * `fields()`, shared by {@see CatalogExportProjection} and {@see CatalogExportQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `catalog-exports` read operations
 */
trait CatalogExportProjecting
{
    /**
     * Sparse fieldsets, with the `catalog-exports` members checked against the descriptor. Other
     * types' fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[catalog-exports]` names a member `catalog-exports` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[CatalogExportBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, CatalogExport::FIELDS, true)) {
                throw UnknownFieldException::for(CatalogExportBase::TYPE, $field, CatalogExport::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

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

    /** @var non-empty-string */
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
     * At most 16 characters.
     *
     * @return non-empty-string
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `format`
     */
    public function format(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('format');

        return $value;
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

    /**
     * This catalog export's id-handle, to write through: its update, delete, relationship writes
     * and actions, exactly as `$client->catalogExports->id(…)` offers them.
     *
     * @throws \LogicException when the catalog export was not read through a client
     */
    public function _handle(): CatalogExportHandle
    {
        return new CatalogExportHandle($this->_fetcher(), $this->id());
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
 * @phpstan-type CatalogExportCreateShape array{format: non-empty-string}
 * @phpstan-type CatalogExportFieldToken 'format'|'status'
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
     * The members `fields[catalog-exports]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['format', 'status'];

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
 * @implements CreateInput<CatalogExportBase>
 *
 * @generated from `POST /catalog-exports`
 */
final class CatalogExportCreate implements CreateInput
{
    /**
     * @param non-empty-string $format
     */
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

    /**
     * Read the resource a write of this input answered with.
     *
     * @param array<string, mixed> $raw
     */
    public function _hydrate(array $raw, ResourceContext $context): CatalogExportBase
    {
        return CatalogExport::_hydrate($raw, $context);
    }
}

/**
 * The fluent create input for `catalog-exports`, for assembly that is conditional. `build()`
 * rejects a missing required member, which this form cannot check statically.
 *
 * @implements CreateInput<CatalogExportBase>
 *
 * @generated from `POST /catalog-exports`
 */
final class CatalogExportCreateBuilder implements CreateInput
{
    use Conditionable;

    /**
     * @var non-empty-string|Missing
     */
    private string|Missing $format = Missing::Value;

    /**
     * At most 16 characters.
     *
     * @param non-empty-string $format
     */
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

    /**
     * The `POST /catalog-exports` request document, from the DTO this builds.
     *
     * @return array<string, mixed>
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function toDocument(): array
    {
        return $this->build()->toDocument();
    }

    /**
     * Read the resource a write of this input answered with.
     *
     * @param array<string, mixed> $raw
     */
    public function _hydrate(array $raw, ResourceContext $context): CatalogExportBase
    {
        return CatalogExport::_hydrate($raw, $context);
    }
}

/**
 * One catalog export by id, before anything has been fetched. Creating the handle performs no IO;
 * each method on it is one request.
 *
 * @generated from the `catalog-exports` operations on one resource
 */
final class CatalogExportHandle
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
     * Fetch the catalog export, shaped by the optional projection: a closure over a fresh
     * projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(CatalogExportProjection<CatalogExportBase>): CatalogExportProjection<TProj>)|CatalogExportProjection<TProj>|null $projection
     *
     * @return ($projection is null ? CatalogExportBase : CatalogExportBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no catalog export has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "catalog-exports",
     *             "id": "1",
     *             "links": {
     *                 "self": {
     *                     "href": "https://music.example/catalog-exports/1",
     *                     "describedby": "/docs.json#/paths/~1catalog-exports~1%7Bid%7D/get"
     *                 }
     *             },
     *             "attributes": {
     *                 "format": "csv",
     *                 "status": "ready"
     *             }
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://music.example/catalog-exports/1",
     *                 "describedby": "/docs.json#/paths/~1catalog-exports~1%7Bid%7D/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs.json#/paths/~1catalog-exports~1%7Bid%7D/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(callable|CatalogExportProjection|null $projection = null): CatalogExportBase
    {
        $query = CatalogExportProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/catalog-exports/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The catalog-exports response', 'data');

        /** @var CatalogExportBase&TProj */
        return $context->hydrate($raw, CatalogExport::_hydrate(...));
    }

    /**
     * Delete the catalog export.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no catalog export has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/catalog-exports/' . $this->id));
    }
}

/**
 * What a response carrying `catalog-exports` should bring back: `include` and `fields`, and nothing
 * else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /catalog-exports/{id}`
 */
final class CatalogExportProjection extends Projection
{
    /** @use CatalogExportProjecting<TProj> */
    use CatalogExportProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<CatalogExportBase>
     */
    public static function make(): self
    {
        /** @var self<CatalogExportBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<CatalogExportBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /catalog-exports`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @generated from `GET /catalog-exports`
 */
final class CatalogExportQuery extends Query
{
    /** @use CatalogExportProjecting<TProj> */
    use CatalogExportProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * `$number` defaults to 1 and `$size` defaults to 15.
     *
     * @param positive-int    $number
     * @param int<1, 50>|null $size
     *
     * @return static
     */
    public function page(int $number, ?int $size = null): static
    {
        return $this->paging($size === null ? ['number' => $number] : ['number' => $number, 'size' => $size]);
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /catalog-exports` is
     * paginated; the runtime keeps it protected.
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
     *
     * @example 200 response:
     *     {
     *         "data": [
     *             {
     *                 "type": "catalog-exports",
     *                 "id": "1",
     *                 "links": {
     *                     "self": {
     *                         "href": "https://music.example/catalog-exports/1",
     *                         "describedby": "/docs.json#/paths/~1catalog-exports~1%7Bid%7D/get"
     *                     }
     *                 },
     *                 "attributes": {
     *                     "format": "csv",
     *                     "status": "ready"
     *                 }
     *             }
     *         ],
     *         "links": {
     *             "self": {
     *                 "href": "https://music.example/catalog-exports?page%5Bnumber%5D=2&page%5Bsize%5D=15",
     *                 "describedby": "/docs.json#/paths/~1catalog-exports/get"
     *             },
     *             "first": {
     *                 "href": "https://music.example/catalog-exports?page%5Bnumber%5D=1&page%5Bsize%5D=15",
     *                 "describedby": "/docs.json#/paths/~1catalog-exports/get"
     *             },
     *             "prev": {
     *                 "href": "https://music.example/catalog-exports?page%5Bnumber%5D=1&page%5Bsize%5D=15",
     *                 "describedby": "/docs.json#/paths/~1catalog-exports/get"
     *             },
     *             "next": {
     *                 "href": "https://music.example/catalog-exports?page%5Bnumber%5D=3&page%5Bsize%5D=15",
     *                 "describedby": "/docs.json#/paths/~1catalog-exports/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs.json#/paths/~1catalog-exports/get"
     *             }
     *         },
     *         "meta": {
     *             "page": {
     *                 "currentPage": 2,
     *                 "perPage": 15,
     *                 "from": 16,
     *                 "to": 30
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): PaginatedCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query), $query);
    }

    /**
     * The first catalog-export the read matches, fetching a single-resource page rather than a
     * whole one.
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
        return Request::get($query->appendTo('/catalog-exports'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $catalogExports = [];

        foreach ($context->primaryList() as $raw) {
            $catalogExports[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $catalogExports,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `CatalogExport`, but
     * `TProj` is a template parameter no bound can prove it satisfies. `ResourceContext::hydrate()`
     * erases the concrete class so the assertion is legal.
     *
     * @param array<string, mixed> $raw
     *
     * @return TProj
     */
    private function hydrate(array $raw, ResourceContext $context): object
    {
        /** @var TProj */
        return $context->hydrate($raw, CatalogExport::_hydrate(...));
    }
}

/**
 * The `catalog-exports` type: every operation its collection endpoint advertises, and the way to
 * one resource by id.
 *
 * @phpstan-import-type CatalogExportCreateShape from CatalogExport
 *
 * @generated from the `catalog-exports` operations
 */
final class CatalogExports
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the catalog-exports collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return CatalogExportQuery<CatalogExportBase>
     */
    public function query(): CatalogExportQuery
    {
        /** @var CatalogExportQuery<CatalogExportBase> */
        return new CatalogExportQuery($this->fetcher);
    }

    /**
     * A handle on one catalog export by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): CatalogExportHandle
    {
        return new CatalogExportHandle($this->fetcher, $id);
    }

    /**
     * Create a catalog export, in any of the three input forms. The server accepts the create for
     * processing and answers with something other than the catalog export, so the response is
     * returned as it came.
     *
     * @param CatalogExportCreate|CatalogExportCreateBuilder|CatalogExportCreateShape $input
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
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "catalog-exports",
     *             "attributes": {
     *                 "format": "csv"
     *             }
     *         }
     *     }
     */
    public function create(CatalogExportCreate|CatalogExportCreateBuilder|array $input): Response
    {
        $document = match (true) {
            $input instanceof CatalogExportCreate => $input,
            $input instanceof CatalogExportCreateBuilder => $input->build(),
            default => CatalogExportCreate::from($input),
        };

        return $this->fetcher->send(Request::post('/catalog-exports', \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)));
    }
}
