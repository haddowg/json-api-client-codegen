<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\FormFields;

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
use haddowg\JsonApiClient\Exceptions\UnknownFilterException;
use haddowg\JsonApiClient\Exceptions\UnknownSortException;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Http\Response;
use haddowg\JsonApiClient\Pagination\Cursor;
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
 * The projection surface of `tags`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see TagProjection} and {@see TagQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `tags` read operations
 */
trait TagProjecting
{
    /**
     * Sparse fieldsets, with the `tags` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[tags]` names a member `tags` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[TagBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Tag::FIELDS, true)) {
                throw UnknownFieldException::for(TagBase::TYPE, $field, Tag::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `tags` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.TagsResource`
 */
abstract class TagBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'tags';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    /** @var non-empty-string */
    public string $label { get => $this->label(); }

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
     * @return non-empty-string
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `label`
     */
    public function label(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('label');

        return $value;
    }

    /**
     * The `articles` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function articlesRef(): array
    {
        return $this->_relationship('articles', false)->identifiers();
    }

    /**
     * Whether the `articles` linkage points at anything. Never throws.
     */
    public function hasArticles(): bool
    {
        return !$this->_relationship('articles', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'articles' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, false);
    }
}

/**
 * A `tags` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `tags` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type TagArticlesInput list<Article|Identifier>
 * @phpstan-type TagCreateShape array{id: string, label: non-empty-string, articles?: TagArticlesInput}
 * @phpstan-type TagUpdateShape array{label?: non-empty-string, articles?: TagArticlesInput}
 * @phpstan-type TagFilterShape array{color?: string, label?: list<string>, excludeId?: string}
 * @phpstan-type TagSortToken '-id'|'id'
 * @phpstan-type TagFieldToken 'articles'|'label'
 *
 * @generated from `components.schemas.TagsResource`
 */
final class Tag extends TagBase
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['label', 'articles'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['label'];

    /**
     * The filters `GET /tags` accepts. The runtime guard on both array doors checks names against
     * this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array FILTERS = ['color', 'label', 'excludeId'];

    /**
     * The signed sort tokens `GET /tags` accepts, both directions of each.
     *
     * @var list<string>
     */
    public const array SORT_TOKENS = ['id', '-id'];

    /**
     * The members `fields[tags]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['label', 'articles'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `tags` one
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
     * A resource identifier for a `tags` resource that is not in hand, for a relationship write.
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
     * @return TagProjection<TagBase>
     */
    public static function projection(): TagProjection
    {
        return TagProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param TagCreateShape|null $input
     *
     * @return ($input is null ? TagCreateBuilder : TagCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `tags` does not have
     */
    public static function create(?array $input = null): TagCreateBuilder|TagCreate
    {
        return $input === null ? new TagCreateBuilder() : TagCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param TagUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `tags` does not have
     */
    public static function update(?array $input = null): TagUpdate
    {
        return $input === null ? new TagUpdate() : TagUpdate::from($input);
    }
}

/**
 * The `articles` relationship of one tag. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `add()`, `remove()`
 * and `replace()`, the methods the relationship endpoint declares, and no others.
 *
 * @generated from `/tags/{id}/articles` and `/tags/{id}/relationships/articles`
 */
final class TagArticlesRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone, a page at a time, without fetching the related resources.
     *
     * @return PaginatedCollection<Identifier, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no tag has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": [
     *             {
     *                 "type": "articles",
     *                 "id": "art-1"
     *             }
     *         ],
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/tags/tag-1/relationships/articles",
     *                 "describedby": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D~1relationships~1articles/get"
     *             },
     *             "related": {
     *                 "href": "https://api.example.com/tags/tag-1/articles",
     *                 "describedby": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D~1articles/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D~1relationships~1articles/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): PaginatedCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/tags/' . $this->id . '/relationships/articles')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Article, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no tag has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/tags/' . $this->id . '/articles')));
    }

    /**
     * Add members to the relationship, leaving the rest alone.
     *
     * @param list<Article|Identifier> $articles
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no tag has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": [
     *             {
     *                 "type": "articles",
     *                 "id": "art-1"
     *             }
     *         ]
     *     }
     */
    public function add(array $articles): void
    {
        $this->fetcher->send(Request::post(
            '/tags/' . $this->id . '/relationships/articles',
            \json_encode(['data' => Coerce::linkageList($articles)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove members from the relationship, leaving the rest alone.
     *
     * @param list<Article|Identifier> $articles
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no tag has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": [
     *             {
     *                 "type": "articles",
     *                 "id": "art-1"
     *             }
     *         ]
     *     }
     */
    public function remove(array $articles): void
    {
        $this->fetcher->send(Request::delete(
            '/tags/' . $this->id . '/relationships/articles',
            \json_encode(['data' => Coerce::linkageList($articles)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Replace every member of the relationship.
     *
     * @param list<Article|Identifier> $articles
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no tag has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": [
     *             {
     *                 "type": "articles",
     *                 "id": "art-1"
     *             }
     *         ]
     *     }
     */
    public function replace(array $articles): void
    {
        $this->fetcher->send(Request::patch(
            '/tags/' . $this->id . '/relationships/articles',
            \json_encode(['data' => Coerce::linkageList($articles)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * @return PaginatedCollection<Identifier, PageNumber>
     */
    private function linkage(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $identifiers = [];

        foreach ($context->primaryList() as $raw) {
            $identifiers[] = Identifier::fromArray($raw, 'the "articles" linkage');
        }

        return new PaginatedCollection(
            $identifiers,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->linkage($this->fetcher->readUrl($url)),
        );
    }

    /**
     * @return PaginatedCollection<Article, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $articles = [];

        foreach ($context->primaryList() as $raw) {
            $articles[] = Article::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $articles,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }
}

/**
 * The canonical create input for `tags`, as named arguments. A missing required member is a static
 * error and an `ArgumentCountError`; every optional member defaults to `Missing`, which keeps an
 * absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type TagCreateShape from Tag
 *
 * @generated from `POST /tags`
 */
final class TagCreate
{
    /**
     * @param non-empty-string $label
     * @param list<Article|Identifier>|Missing $articles
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly array|Missing $articles = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param TagCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `tags`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if ($member !== 'id' && !\in_array($member, Tag::WRITABLE, true)) {
                throw UnknownAttributeException::for(TagBase::TYPE, (string) $member, Tag::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            id: $input['id'],
            label: $input['label'],
            articles: \array_key_exists('articles', $input) ? $input['articles'] : Missing::Value,
        );
    }

    /**
     * The `POST /tags` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => TagBase::TYPE, 'id' => $this->id];

        $attributes = ['label' => $this->label];

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->articles instanceof Missing) {
            $relationships['articles'] = ['data' => Coerce::linkageList($this->articles)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `tags`, for assembly that is conditional. `build()` rejects a missing
 * required member, which this form cannot check statically.
 *
 * @generated from `POST /tags`
 */
final class TagCreateBuilder
{
    use Conditionable;

    private string|Missing $id = Missing::Value;

    /**
     * @var non-empty-string|Missing
     */
    private string|Missing $label = Missing::Value;

    /**
     * @var list<Article|Identifier>|Missing
     */
    private array|Missing $articles = Missing::Value;

    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param non-empty-string $label
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param list<Article|Identifier> $articles
     */
    public function articles(array $articles): self
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): TagCreate
    {
        if ($this->id instanceof Missing) {
            throw MissingRequiredMemberException::for(TagBase::TYPE, ['id']);
        }

        if ($this->label instanceof Missing) {
            throw MissingRequiredMemberException::for(TagBase::TYPE, ['label']);
        }

        return new TagCreate(
            id: $this->id,
            label: $this->label,
            articles: $this->articles,
        );
    }
}

/**
 * One tag by id, before anything has been fetched. Creating the handle performs no IO; each method
 * on it is one request.
 *
 * @phpstan-import-type TagUpdateShape from Tag
 *
 * @generated from the `tags` operations on one resource
 */
final class TagHandle
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
     * Fetch the tag, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(TagProjection<TagBase>): TagProjection<TProj>)|TagProjection<TProj>|null $projection
     *
     * @return ($projection is null ? TagBase : TagBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no tag has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "tags",
     *             "id": "tag-1",
     *             "links": {
     *                 "self": {
     *                     "href": "https://api.example.com/tags/tag-1",
     *                     "describedby": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D/get"
     *                 }
     *             },
     *             "attributes": {
     *                 "label": "php"
     *             },
     *             "relationships": {
     *                 "articles": {
     *                     "links": {
     *                         "self": {
     *                             "href": "https://api.example.com/tags/tag-1/relationships/articles",
     *                             "describedby": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D~1relationships~1articles/get"
     *                         },
     *                         "related": {
     *                             "href": "https://api.example.com/tags/tag-1/articles",
     *                             "describedby": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D~1articles/get"
     *                         }
     *                     },
     *                     "data": [
     *                         {
     *                             "type": "articles",
     *                             "id": "art-1"
     *                         }
     *                     ]
     *                 }
     *             }
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/tags/tag-1",
     *                 "describedby": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1tags~1%7Bid%7D/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(callable|TagProjection|null $projection = null): TagBase
    {
        $query = TagProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/tags/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The tags response', 'data');

        /** @var TagBase&TProj */
        return $context->hydrate($raw, Tag::_hydrate(...));
    }

    /**
     * Patch the tag, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param TagUpdate|TagUpdateShape                                                           $input
     * @param (callable(TagProjection<TagBase>): TagProjection<TProj>)|TagProjection<TProj>|null $projection
     *
     * @return ($projection is null ? TagBase : TagBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no tag has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "tags",
     *             "id": "tag-1",
     *             "attributes": {
     *                 "label": "php"
     *             },
     *             "relationships": {
     *                 "articles": {
     *                     "data": [
     *                         {
     *                             "type": "articles",
     *                             "id": "art-1"
     *                         }
     *                     ]
     *                 }
     *             }
     *         }
     *     }
     */
    public function update(
        TagUpdate|array $input,
        callable|TagProjection|null $projection = null,
    ): TagBase {
        $document = match (true) {
            $input instanceof TagUpdate => $input,
            default => TagUpdate::from($input),
        };

        $query = TagProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/tags/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var TagBase&TProj */
        return $context->hydrate($raw, Tag::_hydrate(...));
    }

    /**
     * Delete the tag.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no tag has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/tags/' . $this->id));
    }

    /**
     * The `articles` relationship, a to-many whose endpoint advertises `add()`, `remove()` and
     * `replace()`.
     */
    public function articles(): TagArticlesRelation
    {
        return new TagArticlesRelation($this->fetcher, $this->id);
    }
}

/**
 * What a response carrying `tags` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /tags/{id}`, `PATCH /tags/{id}`
 */
final class TagProjection extends Projection
{
    /** @use TagProjecting<TProj> */
    use TagProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<TagBase>
     */
    public static function make(): self
    {
        /** @var self<TagBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<TagBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /tags`: the projection, plus everything only a read can carry.
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
 * @phpstan-import-type TagFilterShape from Tag
 * @phpstan-import-type TagSortToken from Tag
 *
 * @generated from `GET /tags`
 */
final class TagQuery extends Query
{
    /** @use TagProjecting<TProj> */
    use TagProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Filter by `color`.
     *
     * @return static
     */
    public function whereColor(string $value): static
    {
        return $this->filtering(['color' => $value]);
    }

    /**
     * Filter by `label`.
     *
     * @param list<string> $value
     *
     * @return static
     */
    public function whereLabel(array $value): static
    {
        return $this->filtering(['label' => $value]);
    }

    /**
     * Filter by `excludeId`.
     *
     * @return static
     */
    public function whereExcludeId(string $value): static
    {
        return $this->filtering(['excludeId' => $value]);
    }

    /**
     * The typed array door: values are checked statically, nested ones included, and names at
     * runtime.
     *
     * @param TagFilterShape $filter
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
     * Sort by the endpoint's own tokens, in precedence order. Argument order is precedence, so
     * nothing is translated between the call and the wire.
     *
     * @param TagSortToken ...$tokens
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
            if (!\in_array($token, Tag::SORT_TOKENS, true)) {
                throw UnknownSortException::for($token, Tag::SORT_TOKENS);
            }
        }

        return $this->sorting($tokens);
    }

    /**
     * Resume after a cursor, as a page's `_page()->after` hands it back.
     *
     * @return static
     */
    public function after(string $cursor): static
    {
        return $this->paging(['after' => $cursor]);
    }

    /**
     * Resume before a cursor, as a page's `_page()->before` hands it back.
     *
     * @return static
     */
    public function before(string $cursor): static
    {
        return $this->paging(['before' => $cursor]);
    }

    /**
     * `$size` defaults to 15.
     *
     * @param int<1, 100> $size
     *
     * @return static
     */
    public function size(int $size): static
    {
        return $this->paging(['size' => $size]);
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /tags` is paginated; the
     * runtime keeps it protected.
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
     * @return PaginatedCollection<TProj, Cursor>
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
     * The first tag the read matches, fetching a single-resource page rather than a whole one.
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
        return $this->size(1)->get()->first();
    }

    /**
     * @param TagFilterShape|array<string, mixed> $filter
     *
     * @return array<string, mixed>
     *
     * @throws UnknownFilterException
     */
    private static function guardFilters(array $filter): array
    {
        foreach (\array_keys($filter) as $name) {
            $name = (string) $name;

            if (!\in_array($name, Tag::FILTERS, true)) {
                throw UnknownFilterException::for($name, Tag::FILTERS);
            }
        }

        return $filter;
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/tags'));
    }

    /**
     * @return PaginatedCollection<TProj, Cursor>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $tags = [];

        foreach ($context->primaryList() as $raw) {
            $tags[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $tags,
            Cursor::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Tag`, but `TProj` is
     * a template parameter no bound can prove it satisfies. `ResourceContext::hydrate()` erases the
     * concrete class so the assertion is legal.
     *
     * @param array<string, mixed> $raw
     *
     * @return TProj
     */
    private function hydrate(array $raw, ResourceContext $context): object
    {
        /** @var TProj */
        return $context->hydrate($raw, Tag::_hydrate(...));
    }
}

/**
 * The canonical update input for `tags`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type TagUpdateShape from Tag
 *
 * @generated from `PATCH /tags/{id}`
 */
final class TagUpdate
{
    use ImmutableConditionable;

    /**
     * @param non-empty-string|Missing $label
     * @param list<Article|Identifier>|Missing $articles
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $label = Missing::Value,
        public readonly array|Missing $articles = Missing::Value,
    ) {}

    /**
     * @param non-empty-string $label
     *
     * @phpstan-pure
     */
    public function label(string $label): self
    {
        return new self(
            label: $label,
            articles: $this->articles,
        );
    }

    /**
     * @param list<Article|Identifier> $articles
     *
     * @phpstan-pure
     */
    public function articles(array $articles): self
    {
        return new self(
            label: $this->label,
            articles: $articles,
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
     * @param TagUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `tags`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Tag::WRITABLE, true)) {
                throw UnknownAttributeException::for(TagBase::TYPE, (string) $member, Tag::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            label: \array_key_exists('label', $input) ? $input['label'] : Missing::Value,
            articles: \array_key_exists('articles', $input) ? $input['articles'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /tags/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => TagBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->label instanceof Missing) {
            $attributes['label'] = $this->label;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->articles instanceof Missing) {
            $relationships['articles'] = ['data' => Coerce::linkageList($this->articles)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The `tags` type: every operation its collection endpoint advertises, and the way to one resource
 * by id.
 *
 * @phpstan-import-type TagCreateShape from Tag
 *
 * @generated from the `tags` operations
 */
final class Tags
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the tags collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return TagQuery<TagBase>
     */
    public function query(): TagQuery
    {
        /** @var TagQuery<TagBase> */
        return new TagQuery($this->fetcher);
    }

    /**
     * A handle on one tag by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): TagHandle
    {
        return new TagHandle($this->fetcher, $id);
    }

    /**
     * Create a tag, in any of the three input forms. The server accepts the create for processing
     * and answers with something other than the tag, so the response is returned as it came.
     *
     * The document requires a client-generated id, carried as the input's `id`.
     *
     * @param TagCreate|TagCreateBuilder|TagCreateShape $input
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
     *             "type": "tags",
     *             "id": "tag-1",
     *             "attributes": {
     *                 "label": "php"
     *             },
     *             "relationships": {
     *                 "articles": {
     *                     "data": [
     *                         {
     *                             "type": "articles",
     *                             "id": "art-1"
     *                         }
     *                     ]
     *                 }
     *             }
     *         }
     *     }
     */
    public function create(TagCreate|TagCreateBuilder|array $input): Response
    {
        $document = match (true) {
            $input instanceof TagCreate => $input,
            $input instanceof TagCreateBuilder => $input->build(),
            default => TagCreate::from($input),
        };

        return $this->fetcher->send(Request::post('/tags', \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)));
    }
}
