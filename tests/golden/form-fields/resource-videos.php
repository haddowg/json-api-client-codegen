<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\FormFields;

use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\NotFound;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
use haddowg\JsonApiClient\Exceptions\UnknownFilterException;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Http\Response;
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
 * The projection surface of `videos`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see VideoProjection} and {@see VideoQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `videos` read operations
 */
trait VideoProjecting
{
    /**
     * Sparse fieldsets, with the `videos` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[videos]` names a member `videos` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[VideoBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Video::FIELDS, true)) {
                throw UnknownFieldException::for(VideoBase::TYPE, $field, Video::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `videos` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.VideosResource`
 */
abstract class VideoBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'videos';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public ?string $url { get => $this->url(); }

    public ?int $seconds { get => $this->seconds(); }

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
     * In `uri` format.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `url`
     */
    public function url(): ?string
    {
        return $this->_nullableString('url');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `seconds`
     */
    public function seconds(): ?int
    {
        return $this->_nullableInt('seconds');
    }
}

/**
 * A `videos` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `videos` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type VideoCreateShape array{url?: string, seconds?: int}
 * @phpstan-type VideoUpdateShape array{url?: string, seconds?: int}
 * @phpstan-type VideoFilterShape array{seconds?: list<int>, id?: list<string>}
 * @phpstan-type VideoFieldToken 'seconds'|'url'
 *
 * @generated from `components.schemas.VideosResource`
 */
final class Video extends VideoBase
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['url', 'seconds'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = [];

    /**
     * The filters `GET /videos` accepts. The runtime guard on both array doors checks names against
     * this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array FILTERS = ['seconds', 'id'];

    /**
     * The members `fields[videos]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['url', 'seconds'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `videos` one
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
     * A resource identifier for a `videos` resource that is not in hand, for a relationship write.
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
     * @return VideoProjection<VideoBase>
     */
    public static function projection(): VideoProjection
    {
        return VideoProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param VideoCreateShape|null $input
     *
     * @return ($input is null ? VideoCreateBuilder : VideoCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `videos` does not have
     */
    public static function create(?array $input = null): VideoCreateBuilder|VideoCreate
    {
        return $input === null ? new VideoCreateBuilder() : VideoCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param VideoUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `videos` does not have
     */
    public static function update(?array $input = null): VideoUpdate
    {
        return $input === null ? new VideoUpdate() : VideoUpdate::from($input);
    }
}

/**
 * The canonical create input for `videos`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type VideoCreateShape from Video
 *
 * @generated from `POST /videos`
 */
final class VideoCreate
{
    public function __construct(
        public readonly string|Missing $url = Missing::Value,
        public readonly int|Missing $seconds = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param VideoCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `videos`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Video::WRITABLE, true)) {
                throw UnknownAttributeException::for(VideoBase::TYPE, (string) $member, Video::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            url: \array_key_exists('url', $input) ? $input['url'] : Missing::Value,
            seconds: \array_key_exists('seconds', $input) ? $input['seconds'] : Missing::Value,
        );
    }

    /**
     * The `POST /videos` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => VideoBase::TYPE];

        $attributes = [];

        if (!$this->url instanceof Missing) {
            $attributes['url'] = $this->url;
        }

        if (!$this->seconds instanceof Missing) {
            $attributes['seconds'] = $this->seconds;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `videos`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /videos`
 */
final class VideoCreateBuilder
{
    use Conditionable;

    private string|Missing $url = Missing::Value;

    private int|Missing $seconds = Missing::Value;

    /**
     * In `uri` format.
     */
    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function seconds(int $seconds): self
    {
        $this->seconds = $seconds;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     */
    public function build(): VideoCreate
    {
        return new VideoCreate(
            url: $this->url,
            seconds: $this->seconds,
        );
    }
}

/**
 * One video by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type VideoUpdateShape from Video
 *
 * @generated from the `videos` operations on one resource
 */
final class VideoHandle
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
     * Fetch the video, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(VideoProjection<VideoBase>): VideoProjection<TProj>)|VideoProjection<TProj>|null $projection
     *
     * @return ($projection is null ? VideoBase : VideoBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no video has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|VideoProjection|null $projection = null): VideoBase
    {
        $query = VideoProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/videos/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The videos response', 'data');

        /** @var VideoBase&TProj */
        return $context->hydrate($raw, Video::_hydrate(...));
    }

    /**
     * Patch the video, in either input form. The server answers with something other than the
     * video, so the response is returned as it came.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @param VideoUpdate|VideoUpdateShape $input
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no video has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function update(VideoUpdate|array $input): Response
    {
        $document = match (true) {
            $input instanceof VideoUpdate => $input,
            default => VideoUpdate::from($input),
        };

        return $this->fetcher->send(Request::patch(
            '/videos/' . $this->id,
            \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Delete the video.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no video has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/videos/' . $this->id));
    }
}

/**
 * What a response carrying `videos` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /videos/{id}`, `POST /videos`
 */
final class VideoProjection extends Projection
{
    /** @use VideoProjecting<TProj> */
    use VideoProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<VideoBase>
     */
    public static function make(): self
    {
        /** @var self<VideoBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<VideoBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /videos`: the projection, plus everything only a read can carry.
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
 * @phpstan-import-type VideoFilterShape from Video
 *
 * @generated from `GET /videos`
 */
final class VideoQuery extends Query
{
    /** @use VideoProjecting<TProj> */
    use VideoProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Filter by `seconds`.
     *
     * @param list<int> $value
     *
     * @return static
     */
    public function whereSeconds(array $value): static
    {
        return $this->filtering(['seconds' => $value]);
    }

    /**
     * Filter by `id`.
     *
     * @param list<string> $value
     *
     * @return static
     */
    public function whereId(array $value): static
    {
        return $this->filtering(['id' => $value]);
    }

    /**
     * The typed array door: values are checked statically, nested ones included, and names at
     * runtime.
     *
     * @param VideoFilterShape $filter
     *
     * @return static
     *
     * @throws UnknownFilterException when a name is not one this endpoint filters by
     */
    public function filter(array $filter): static
    {
        return $this->filtering(self::guardFilters($filter));
    }

    /**
     * The loose door, for a filter map assembled at runtime. Nothing is checked statically; the
     * names are still checked here.
     *
     * @param array<string, mixed> $filter
     *
     * @return static
     *
     * @throws UnknownFilterException when a name is not one this endpoint filters by
     */
    public function filterRaw(array $filter): static
    {
        return $this->filtering(self::guardFilters($filter));
    }

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
     * The first video the read matches.
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

    /**
     * @param VideoFilterShape|array<string, mixed> $filter
     *
     * @return array<string, mixed>
     *
     * @throws UnknownFilterException
     */
    private static function guardFilters(array $filter): array
    {
        foreach (\array_keys($filter) as $name) {
            $name = (string) $name;

            if (!\in_array($name, Video::FILTERS, true)) {
                throw UnknownFilterException::for($name, Video::FILTERS);
            }
        }

        return $filter;
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/videos'));
    }

    /**
     * @return ResourceCollection<TProj>
     */
    private function collect(ResourceContext $context): ResourceCollection
    {
        $document = $context->document();
        $videos = [];

        foreach ($context->primaryList() as $raw) {
            $videos[] = $this->hydrate($raw, $context);
        }

        return new ResourceCollection($videos, $document->meta(), $document->links());
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Video`, but `TProj`
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
        return $context->hydrate($raw, Video::_hydrate(...));
    }
}

/**
 * The canonical update input for `videos`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type VideoUpdateShape from Video
 *
 * @generated from `PATCH /videos/{id}`
 */
final class VideoUpdate
{
    use ImmutableConditionable;

    /**
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $url = Missing::Value,
        public readonly int|Missing $seconds = Missing::Value,
    ) {}

    /**
     * In `uri` format.
     *
     * @phpstan-pure
     */
    public function url(string $url): self
    {
        return new self(
            url: $url,
            seconds: $this->seconds,
        );
    }

    /**
     * @phpstan-pure
     */
    public function seconds(int $seconds): self
    {
        return new self(
            url: $this->url,
            seconds: $seconds,
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
     * @param VideoUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `videos`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Video::WRITABLE, true)) {
                throw UnknownAttributeException::for(VideoBase::TYPE, (string) $member, Video::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            url: \array_key_exists('url', $input) ? $input['url'] : Missing::Value,
            seconds: \array_key_exists('seconds', $input) ? $input['seconds'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /videos/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => VideoBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->url instanceof Missing) {
            $attributes['url'] = $this->url;
        }

        if (!$this->seconds instanceof Missing) {
            $attributes['seconds'] = $this->seconds;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        return ['data' => $data];
    }
}

/**
 * The `videos` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type VideoCreateShape from Video
 *
 * @generated from the `videos` operations
 */
final class Videos
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the videos collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return VideoQuery<VideoBase>
     */
    public function query(): VideoQuery
    {
        /** @var VideoQuery<VideoBase> */
        return new VideoQuery($this->fetcher);
    }

    /**
     * A handle on one video by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): VideoHandle
    {
        return new VideoHandle($this->fetcher, $id);
    }

    /**
     * Create a video, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param VideoCreate|VideoCreateBuilder|VideoCreateShape                                            $input
     * @param (callable(VideoProjection<VideoBase>): VideoProjection<TProj>)|VideoProjection<TProj>|null $projection
     *
     * @return ($projection is null ? VideoBase : VideoBase&TProj)
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
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function create(
        VideoCreate|VideoCreateBuilder|array $input,
        callable|VideoProjection|null $projection = null,
    ): VideoBase {
        $document = match (true) {
            $input instanceof VideoCreate => $input,
            $input instanceof VideoCreateBuilder => $input->build(),
            default => VideoCreate::from($input),
        };

        $query = VideoProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/videos'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var VideoBase&TProj */
        return $context->hydrate($raw, Video::_hydrate(...));
    }
}
