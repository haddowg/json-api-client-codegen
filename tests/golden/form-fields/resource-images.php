<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\FormFields;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Collections\ResourceCollection;
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
use haddowg\JsonApiClient\Resources\DeclaredLink;
use haddowg\JsonApiClient\Resources\DeclaredLinks;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\LinkMeta;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * The projection surface of `images`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see ImageProjection} and {@see ImageQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `images` read operations
 */
trait ImageProjecting
{
    /**
     * Sparse fieldsets, with the `images` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[images]` names a member `images` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[ImageBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Image::FIELDS, true)) {
                throw UnknownFieldException::for(ImageBase::TYPE, $field, Image::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * An `images` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.ImagesResource`
 */
abstract class ImageBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'images';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public ?string $url { get => $this->url(); }

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
     * In `uri` format.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `url`
     */
    public function url(): ?string
    {
        return $this->_nullableString('url');
    }

    /**
     * The `clips` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function clipsRef(): array
    {
        return $this->_relationship('clips', false)->identifiers();
    }

    /**
     * Whether the `clips` linkage points at anything. Never throws.
     */
    public function hasClips(): bool
    {
        return !$this->_relationship('clips', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'clips' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, false);
    }

    /**
     * The links an `images` resource declares, each typed. A link the response left out reads as
     * null. Every link, declared or not, is in `_links()`.
     */
    public function _declaredLinks(): ImageLinks
    {
        return new ImageLinks($this->_links(), $this->_subject());
    }
}

/**
 * An `images` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `images` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type ImageFieldToken 'clips'|'url'
 * @phpstan-type ImageCountToken 'clips'
 *
 * @generated from `components.schemas.ImagesResource`
 */
final class Image extends ImageBase
{
    /**
     * The members `fields[images]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['url', 'clips'];

    /**
     * The `withCount` tokens `GET /images` advertises.
     *
     * @var list<string>
     */
    public const array COUNT_TOKENS = ['clips'];

    /**
     * The Countable profile, which the client adds to `Accept` only when a request uses
     * `withCount`.
     */
    public const string COUNTABLE_PROFILE = 'https://haddowg.github.io/json-api/profiles/countable/';

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `images` one
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
     * A resource identifier for an `images` resource that is not in hand, for a relationship write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }
}

/**
 * The `clips` relationship of one image. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. No write is advertised, so none is
 * generated.
 *
 * @generated from `/images/{id}/clips` and `/images/{id}/relationships/clips`
 */
final class ImageClipsRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone, without fetching the related resources.
     *
     * @return ResourceCollection<Identifier>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no image has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": [
     *             {
     *                 "type": "videos",
     *                 "id": "vid-1"
     *             }
     *         ],
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/images/img-1/relationships/clips",
     *                 "describedby": "/docs/openapi.json#/paths/~1images~1%7Bid%7D~1relationships~1clips/get"
     *             },
     *             "related": {
     *                 "href": "https://api.example.com/images/img-1/clips",
     *                 "describedby": "/docs/openapi.json#/paths/~1images~1%7Bid%7D~1clips/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1images~1%7Bid%7D~1relationships~1clips/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): ResourceCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/images/' . $this->id . '/relationships/clips')),
        );
    }

    /**
     * The related resources themselves.
     *
     * @return ResourceCollection<Video>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no image has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": [
     *             {
     *                 "type": "videos",
     *                 "id": "vid-1",
     *                 "links": {
     *                     "self": {
     *                         "href": "https://api.example.com/videos/vid-1",
     *                         "describedby": "/docs/openapi.json#/paths/~1videos~1%7Bid%7D/get"
     *                     }
     *                 },
     *                 "attributes": {
     *                     "url": "https://cdn.example.com/intro.mp4",
     *                     "seconds": 90
     *                 }
     *             }
     *         ],
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/images/img-1/clips",
     *                 "describedby": "/docs/openapi.json#/paths/~1images~1%7Bid%7D~1clips/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1images~1%7Bid%7D~1clips/get"
     *             }
     *         },
     *         "meta": {
     *             "total": 1
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function related(): ResourceCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/images/' . $this->id . '/clips')));
    }

    /**
     * @return ResourceCollection<Identifier>
     */
    private function linkage(ResourceContext $context): ResourceCollection
    {
        $document = $context->document();
        $identifiers = [];

        foreach ($context->primaryList() as $raw) {
            $identifiers[] = Identifier::fromArray($raw, 'the "clips" linkage');
        }

        return new ResourceCollection($identifiers, $document->meta(), $document->links());
    }

    /**
     * @return ResourceCollection<Video>
     */
    private function resources(ResourceContext $context): ResourceCollection
    {
        $document = $context->document();
        $clips = [];

        foreach ($context->primaryList() as $raw) {
            $clips[] = Video::_hydrate($raw, $context);
        }

        return new ResourceCollection($clips, $document->meta(), $document->links());
    }
}

/**
 * The `download` link of an `images` resource. Where to fetch the original file.
 *
 * @generated from `components.schemas.ImagesResourceLinks.properties.download`
 */
final class ImageDownloadLink extends DeclaredLink
{
    public string $href { get => $this->href(); }
    public string $title { get => $this->title(); }
    public string $type { get => $this->type(); }
    public ImageDownloadLinkMeta $meta { get => $this->meta(); }

    /**
     * Always `Original`.
     */
    public function title(): string
    {
        return $this->_string('title');
    }

    /**
     * Always `image/png`.
     */
    public function type(): string
    {
        return $this->_string('type');
    }

    /**
     * The link's `meta`, each member typed.
     */
    public function meta(): ImageDownloadLinkMeta
    {
        return $this->_metaAs(ImageDownloadLinkMeta::class);
    }
}

/**
 * The `meta` of the `download` link of an `images` resource.
 *
 * A member the server always renders reads as its type. Any other reads as nullable, because a
 * request can hide it and the response then leaves it out.
 *
 * @generated from `components.schemas.ImagesResourceLinks.properties.download.properties.meta`
 */
final class ImageDownloadLinkMeta extends LinkMeta
{
    public string $method { get => $this->method(); }
    public \DateTimeImmutable $expiresAt { get => $this->expiresAt(); }
    /** @var array<string, mixed>|null */
    public ?array $headers { get => $this->headers(); }

    /**
     * The HTTP method to use.
     *
     * Example: `"GET"`
     */
    public function method(): string
    {
        return $this->_string('method');
    }

    /**
     * When the URL stops working.
     *
     * Example: `"2026-10-26T12:00:00+00:00"`
     */
    public function expiresAt(): \DateTimeImmutable
    {
        return $this->_dateTime('expiresAt');
    }

    /**
     * Example: `{"Range":"bytes=0-"}`
     *
     * @return array<string, mixed>|null
     */
    public function headers(): ?array
    {
        return $this->_nullableMap('headers');
    }
}

/**
 * One image by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @generated from the `images` operations on one resource
 */
final class ImageHandle
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
     * Fetch the image, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(ImageProjection<ImageBase>): ImageProjection<TProj>)|ImageProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ImageBase : ImageBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no image has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "images",
     *             "id": "img-1",
     *             "links": {
     *                 "self": {
     *                     "href": "https://api.example.com/images/img-1",
     *                     "describedby": "/docs/openapi.json#/paths/~1images~1%7Bid%7D/get"
     *                 },
     *                 "download": {
     *                     "href": "https://cdn.example.com/originals/img-1.png",
     *                     "title": "Original",
     *                     "type": "image/png",
     *                     "meta": {
     *                         "method": "GET",
     *                         "expiresAt": "2026-10-26T12:00:00+00:00",
     *                         "headers": {
     *                             "Range": "bytes=0-"
     *                         }
     *                     }
     *                 },
     *                 "thumbnail": "https://cdn.example.com/thumbs/img-1.png"
     *             },
     *             "attributes": {
     *                 "url": "https://cdn.example.com/cover.png"
     *             },
     *             "relationships": {
     *                 "clips": {
     *                     "links": {
     *                         "self": {
     *                             "href": "https://api.example.com/images/img-1/relationships/clips",
     *                             "describedby": "/docs/openapi.json#/paths/~1images~1%7Bid%7D~1relationships~1clips/get"
     *                         },
     *                         "related": {
     *                             "href": "https://api.example.com/images/img-1/clips",
     *                             "describedby": "/docs/openapi.json#/paths/~1images~1%7Bid%7D~1clips/get"
     *                         }
     *                     },
     *                     "data": [
     *                         {
     *                             "type": "videos",
     *                             "id": "vid-1"
     *                         }
     *                     ]
     *                 }
     *             }
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/images/img-1",
     *                 "describedby": "/docs/openapi.json#/paths/~1images~1%7Bid%7D/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1images~1%7Bid%7D/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(callable|ImageProjection|null $projection = null): ImageBase
    {
        $query = ImageProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/images/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The images response', 'data');

        /** @var ImageBase&TProj */
        return $context->hydrate($raw, Image::_hydrate(...));
    }

    /**
     * The `clips` relationship, a to-many the document advertises no write for.
     */
    public function clips(): ImageClipsRelation
    {
        return new ImageClipsRelation($this->fetcher, $this->id);
    }
}

/**
 * The links an `images` resource declares, one typed accessor each.
 *
 * Every declared link is optional, because the server leaves one out when it does not apply, so
 * each accessor returns null for a link the response left out. A link the type does not declare is
 * in the resource's `_links()`.
 *
 * @generated from `components.schemas.ImagesResourceLinks`
 */
final class ImageLinks extends DeclaredLinks
{
    public ?ImageDownloadLink $download { get => $this->download(); }
    public ?string $thumbnail { get => $this->thumbnail(); }

    /**
     * Where to fetch the original file.
     */
    public function download(): ?ImageDownloadLink
    {
        return $this->_object('download', ImageDownloadLink::class);
    }

    /**
     * The `thumbnail` link, absent when it does not apply to the resource.
     *
     * A bare link, read as its URL from either a string or a link object.
     */
    public function thumbnail(): ?string
    {
        return $this->_href('thumbnail');
    }
}

/**
 * What a response carrying `images` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /images/{id}`
 */
final class ImageProjection extends Projection
{
    /** @use ImageProjecting<TProj> */
    use ImageProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<ImageBase>
     */
    public static function make(): self
    {
        /** @var self<ImageBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<ImageBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /images`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * @template TProj of object
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type ImageCountToken from Image
 *
 * @generated from `GET /images`
 */
final class ImageQuery extends Query
{
    /** @use ImageProjecting<TProj> */
    use ImageProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Count related resources without including them, under the Countable profile. `GET /images`
     * advertises `clips`. A counted relation is still not an included one: its count is read
     * through `_rel()`.
     *
     * @param list<ImageCountToken> $tokens
     *
     * @return static
     */
    public function withCount(array $tokens): static
    {
        return $this->counting($tokens);
    }

    /**
     * `$number` defaults to 1 and `$size` defaults to 15.
     *
     * @param positive-int     $number
     * @param int<1, 100>|null $size
     *
     * @return static
     */
    public function page(int $number, ?int $size = null): static
    {
        return $this->paging($size === null ? ['number' => $number] : ['number' => $number, 'size' => $size]);
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /images` is paginated;
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
     * The first image the read matches, fetching a single-resource page rather than a whole one.
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

    /**
     * The Countable profile is negotiated only when a request actually uses a `withCount`
     * parameter, which is what `x-profile` on that parameter states.
     */
    private function request(ReadQuery $query): Request
    {
        $request = Request::get($query->appendTo('/images'));

        return $query->withCount === [] ? $request : $request->withProfiles([Image::COUNTABLE_PROFILE]);
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $images = [];

        foreach ($context->primaryList() as $raw) {
            $images[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $images,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Image`, but `TProj`
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
        return $context->hydrate($raw, Image::_hydrate(...));
    }
}

/**
 * The `images` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @generated from the `images` operations
 */
final class Images
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the images collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return ImageQuery<ImageBase>
     */
    public function query(): ImageQuery
    {
        /** @var ImageQuery<ImageBase> */
        return new ImageQuery($this->fetcher);
    }

    /**
     * A handle on one image by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): ImageHandle
    {
        return new ImageHandle($this->fetcher, $id);
    }
}
