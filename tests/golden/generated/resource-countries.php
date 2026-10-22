<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\NotFound;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Query\Query;
use haddowg\JsonApiClient\Query\ReadQuery;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * A `countries` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * @generated from `components.schemas.CountriesResource`
 */
abstract class CountryBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'countries';

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
}

/**
 * A `countries` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `countries` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @generated from `components.schemas.CountriesResource`
 */
final class Country extends CountryBase
{
    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `countries` one
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
     * A resource identifier for a `countries` resource that is not in hand, for a relationship
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
 * The `countries` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @generated from the `countries` operations
 */
final class Countries
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the countries collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return CountryQuery<CountryBase>
     */
    public function query(): CountryQuery
    {
        /** @var CountryQuery<CountryBase> */
        return new CountryQuery($this->fetcher);
    }

    /**
     * A handle on one country by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): CountryHandle
    {
        return new CountryHandle($this->fetcher, $id);
    }
}

/**
 * One country by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @generated from the `countries` operations on one resource
 */
final class CountryHandle
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
     * Fetch the country. The endpoint advertises no `include` or `fields`, so there is nothing to
     * project.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no country has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(): CountryBase
    {
        $context = $this->fetcher->read(Request::get('/countries/' . $this->id));
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The countries response', 'data');

        return Country::_hydrate($raw, $context);
    }
}

/**
 * A read of `GET /countries`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @generated from `GET /countries`
 */
final class CountryQuery extends Query
{
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Run the read.
     *
     * @return ResourceCollection<TProj>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ResourceCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query));
    }

    /**
     * The first country the read matches.
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
        return $this->get()->first();
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/countries'));
    }

    /**
     * @return ResourceCollection<TProj>
     */
    private function collect(ResourceContext $context): ResourceCollection
    {
        $document = $context->document();
        $countries = [];

        foreach ($context->primaryList() as $raw) {
            $countries[] = $this->hydrate($raw, $context);
        }

        return new ResourceCollection($countries, $document->meta(), $document->links());
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Country`, but `TProj`
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
        return $context->hydrate($raw, Country::_hydrate(...));
    }
}
