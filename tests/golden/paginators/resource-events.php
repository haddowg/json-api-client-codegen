<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\Paginators;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\Offset;
use haddowg\JsonApiClient\Pagination\PageLinks;
use haddowg\JsonApiClient\Query\Query;
use haddowg\JsonApiClient\Query\ReadQuery;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * An `events` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.EventsResource`
 */
abstract class EventBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'events';

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
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        return $this->_string('name');
    }
}

/**
 * An `events` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `events` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @generated from `components.schemas.EventsResource`
 */
final class Event extends EventBase
{
    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `events` one
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
     * A resource identifier for an `events` resource that is not in hand, for a relationship write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }
}

/**
 * A read of `GET /events`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @generated from `GET /events`
 */
final class EventQuery extends Query
{
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * @param int<0, max>       $offset
     * @param positive-int|null $limit
     *
     * @return static
     */
    public function page(int $offset, ?int $limit = null): static
    {
        return $this->paging($limit === null ? ['skip' => $offset] : ['skip' => $offset, 'take' => $limit]);
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /events` is paginated;
     * the runtime keeps it protected.
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
     * @return PaginatedCollection<TProj, Offset>
     *
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query), $query);
    }

    /**
     * The first event the read matches, fetching a single-resource page rather than a whole one.
     *
     * @return TProj|null
     *
     * @throws TransportException when no response is received at all
     */
    public function first(): ?object
    {
        return $this->page(0, 1)->get()->first();
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/events'));
    }

    /**
     * @return PaginatedCollection<TProj, Offset>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $events = [];

        foreach ($context->primaryList() as $raw) {
            $events[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $events,
            Offset::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Event`, but `TProj`
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
        return $context->hydrate($raw, Event::_hydrate(...));
    }
}

/**
 * The `events` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @generated from the `events` operations
 */
final class Events
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the events collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return EventQuery<EventBase>
     */
    public function query(): EventQuery
    {
        /** @var EventQuery<EventBase> */
        return new EventQuery($this->fetcher);
    }
}
