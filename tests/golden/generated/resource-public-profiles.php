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
use haddowg\JsonApiClient\Exceptions\UnknownSortException;
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
 * The projection surface of `public-profiles`: the `withX()` include methods and the checked
 * `fields()`, shared by {@see PublicProfileProjection} and {@see PublicProfileQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `public-profiles` read operations
 */
trait PublicProfileProjecting
{
    /**
     * Sparse fieldsets, with the `public-profiles` members checked against the descriptor. Other
     * types' fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[public-profiles]` names a member `public-profiles` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[PublicProfileBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, PublicProfile::FIELDS, true)) {
                throw UnknownFieldException::for(PublicProfileBase::TYPE, $field, PublicProfile::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `public-profiles` resource with no relation hydrated: everything a read is guaranteed to give
 * you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.PublicProfilesResource`
 */
abstract class PublicProfileBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'public-profiles';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $displayName { get => $this->displayName(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `displayName`
     */
    public function displayName(): string
    {
        return $this->_string('displayName');
    }
}

/**
 * A `public-profiles` resource as it actually arrives: every attribute, and every relation
 * accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `public-profiles` surface accepts, and the descriptor
 * constants the runtime guards check against. `@phpstan-type` aliases are class-local, so every
 * other class using one imports it from here.
 *
 * @phpstan-type PublicProfileSortToken '-displayName'|'displayName'
 * @phpstan-type PublicProfileFieldToken 'displayName'
 *
 * @generated from `components.schemas.PublicProfilesResource`
 */
final class PublicProfile extends PublicProfileBase
{
    /**
     * The signed sort tokens `GET /public-profiles` accepts, both directions of each.
     *
     * @var list<string>
     */
    public const array SORT_TOKENS = ['displayName', '-displayName'];

    /**
     * The members `fields[public-profiles]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['displayName'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `public-profiles` one
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
     * A resource identifier for a `public-profiles` resource that is not in hand, for a
     * relationship write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }
}

/**
 * One public profile by id, before anything has been fetched. Creating the handle performs no IO;
 * each method on it is one request.
 *
 * @generated from the `public-profiles` operations on one resource
 */
final class PublicProfileHandle
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
     * Fetch the public profile, shaped by the optional projection: a closure over a fresh
     * projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(PublicProfileProjection<PublicProfileBase>): PublicProfileProjection<TProj>)|PublicProfileProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PublicProfileBase : PublicProfileBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no public profile has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|PublicProfileProjection|null $projection = null): PublicProfileBase
    {
        $query = PublicProfileProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/public-profiles/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The public-profiles response', 'data');

        /** @var PublicProfileBase&TProj */
        return $context->hydrate($raw, PublicProfile::_hydrate(...));
    }
}

/**
 * What a response carrying `public-profiles` should bring back: `include` and `fields`, and nothing
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
 * @generated from the `include` and `fields` parameters of `GET /public-profiles/{id}`
 */
final class PublicProfileProjection extends Projection
{
    /** @use PublicProfileProjecting<TProj> */
    use PublicProfileProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<PublicProfileBase>
     */
    public static function make(): self
    {
        /** @var self<PublicProfileBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<PublicProfileBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /public-profiles`: the projection, plus everything only a read can carry.
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
 * @phpstan-import-type PublicProfileSortToken from PublicProfile
 *
 * @generated from `GET /public-profiles`
 */
final class PublicProfileQuery extends Query
{
    /** @use PublicProfileProjecting<TProj> */
    use PublicProfileProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Sort by the endpoint's own tokens, in precedence order. Argument order is precedence, so
     * nothing is translated between the call and the wire.
     *
     * @param PublicProfileSortToken ...$tokens
     *
     * @return static
     */
    public function sort(string ...$tokens): static
    {
        return $this->sorting(\array_values($tokens));
    }

    /**
     * The loose sort door, for tokens assembled at runtime, which a literal union can never accept.
     *
     * @param list<string> $tokens
     *
     * @return static
     *
     * @throws UnknownSortException when a token is not one this endpoint sorts by
     */
    public function sortRaw(array $tokens): static
    {
        foreach ($tokens as $token) {
            if (!\in_array($token, PublicProfile::SORT_TOKENS, true)) {
                throw UnknownSortException::for($token, PublicProfile::SORT_TOKENS);
            }
        }

        return $this->sorting($tokens);
    }

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
     * The first public-profile the read matches, fetching a single-resource page rather than a
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
        return Request::get($query->appendTo('/public-profiles'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $publicProfiles = [];

        foreach ($context->primaryList() as $raw) {
            $publicProfiles[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $publicProfiles,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `PublicProfile`, but
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
        return $context->hydrate($raw, PublicProfile::_hydrate(...));
    }
}

/**
 * The `public-profiles` type: every operation its collection endpoint advertises, and the way to
 * one resource by id.
 *
 * @generated from the `public-profiles` operations
 */
final class PublicProfiles
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the public-profiles collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return PublicProfileQuery<PublicProfileBase>
     */
    public function query(): PublicProfileQuery
    {
        /** @var PublicProfileQuery<PublicProfileBase> */
        return new PublicProfileQuery($this->fetcher);
    }

    /**
     * A handle on one public profile by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): PublicProfileHandle
    {
        return new PublicProfileHandle($this->fetcher, $id);
    }
}
