<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\Paginators;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\FixedPage;
use haddowg\JsonApiClient\Pagination\PageLinks;
use haddowg\JsonApiClient\Query\Query;
use haddowg\JsonApiClient\Query\ReadQuery;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * A `chapters` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.ChaptersResource`
 */
abstract class ChapterBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'chapters';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $heading { get => $this->heading(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `heading`
     */
    public function heading(): string
    {
        return $this->_string('heading');
    }
}

/**
 * A `chapters` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `chapters` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @generated from `components.schemas.ChaptersResource`
 */
final class Chapter extends ChapterBase
{
    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `chapters` one
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
     * A resource identifier for a `chapters` resource that is not in hand, for a relationship
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
 * A read of `GET /chapters`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @generated from `GET /chapters`
 */
final class ChapterQuery extends Query
{
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Select a page. The server fixes the page size, so there is no size to set.
     *
     * @param positive-int $number
     *
     * @return static
     */
    public function page(int $number): static
    {
        return $this->paging(['p' => $number]);
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /chapters` is paginated;
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
     * @return PaginatedCollection<TProj, FixedPage>
     *
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query), $query);
    }

    /**
     * The first chapter the read matches.
     *
     * @return TProj|null
     *
     * @throws TransportException when no response is received at all
     */
    public function first(): ?object
    {
        return $this->page(1)->get()->first();
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/chapters'));
    }

    /**
     * @return PaginatedCollection<TProj, FixedPage>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $chapters = [];

        foreach ($context->primaryList() as $raw) {
            $chapters[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $chapters,
            FixedPage::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Chapter`, but `TProj`
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
        return $context->hydrate($raw, Chapter::_hydrate(...));
    }
}

/**
 * The `chapters` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @generated from the `chapters` operations
 */
final class Chapters
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the chapters collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return ChapterQuery<ChapterBase>
     */
    public function query(): ChapterQuery
    {
        /** @var ChapterQuery<ChapterBase> */
        return new ChapterQuery($this->fetcher);
    }
}
