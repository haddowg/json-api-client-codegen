<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\NotFound;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
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
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * The projection surface of `export-jobs`: the `withX()` include methods and the checked
 * `fields()`, shared by {@see ExportJobProjection} and {@see ExportJobQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `export-jobs` read operations
 */
trait ExportJobProjecting
{
    /**
     * Sparse fieldsets, with the `export-jobs` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[export-jobs]` names a member `export-jobs` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[ExportJobBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, ExportJob::FIELDS, true)) {
                throw UnknownFieldException::for(ExportJobBase::TYPE, $field, ExportJob::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * An `export-jobs` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.ExportJobsResource`
 */
abstract class ExportJobBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'export-jobs';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $state { get => $this->state(); }

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
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `state`
     */
    public function state(): string
    {
        return $this->_string('state');
    }
}

/**
 * An `export-jobs` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `export-jobs` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @phpstan-type ExportJobFieldToken 'state'
 *
 * @generated from `components.schemas.ExportJobsResource`
 */
final class ExportJob extends ExportJobBase
{
    /**
     * The members `fields[export-jobs]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['state'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `export-jobs` one
     */
    public static function _hydrate(array $raw, ResourceContext $context): self
    {
        $identifier = Identifier::fromArray($raw, 'an "' . self::TYPE . '" resource object');

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
     * A resource identifier for an `export-jobs` resource that is not in hand, for a relationship
     * write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }
}

/**
 * One export job by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @generated from the `export-jobs` operations on one resource
 */
final class ExportJobHandle
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
     * Fetch the export job, shaped by the optional projection: a closure over a fresh projection,
     * or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(ExportJobProjection<ExportJobBase>): ExportJobProjection<TProj>)|ExportJobProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ExportJobBase : ExportJobBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no export job has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|ExportJobProjection|null $projection = null): ExportJobBase
    {
        $query = ExportJobProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/export-jobs/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The export-jobs response', 'data');

        /** @var ExportJobBase&TProj */
        return $context->hydrate($raw, ExportJob::_hydrate(...));
    }
}

/**
 * What a response carrying `export-jobs` should bring back: `include` and `fields`, and nothing
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
 * @generated from the `include` and `fields` parameters of `GET /export-jobs/{id}`
 */
final class ExportJobProjection extends Projection
{
    /** @use ExportJobProjecting<TProj> */
    use ExportJobProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<ExportJobBase>
     */
    public static function make(): self
    {
        /** @var self<ExportJobBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<ExportJobBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /export-jobs`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @generated from `GET /export-jobs`
 */
final class ExportJobQuery extends Query
{
    /** @use ExportJobProjecting<TProj> */
    use ExportJobProjecting;
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
     * Paginator parameters assembled at runtime. Public here because `GET /export-jobs` is
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
     */
    public function get(): PaginatedCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query), $query);
    }

    /**
     * The first export-job the read matches, fetching a single-resource page rather than a whole
     * one.
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
        return Request::get($query->appendTo('/export-jobs'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $exportJobs = [];

        foreach ($context->primaryList() as $raw) {
            $exportJobs[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $exportJobs,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `ExportJob`, but
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
        return $context->hydrate($raw, ExportJob::_hydrate(...));
    }
}

/**
 * The `export-jobs` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @generated from the `export-jobs` operations
 */
final class ExportJobs
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the export-jobs collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return ExportJobQuery<ExportJobBase>
     */
    public function query(): ExportJobQuery
    {
        /** @var ExportJobQuery<ExportJobBase> */
        return new ExportJobQuery($this->fetcher);
    }

    /**
     * A handle on one export job by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): ExportJobHandle
    {
        return new ExportJobHandle($this->fetcher, $id);
    }
}
