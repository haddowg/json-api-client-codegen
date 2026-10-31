<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\Paginators;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\Cursor;
use haddowg\JsonApiClient\Pagination\Offset;
use haddowg\JsonApiClient\Pagination\Page;
use haddowg\JsonApiClient\Pagination\PageLinks;
use haddowg\JsonApiClient\Pagination\PageNumber;
use haddowg\JsonApiClient\Query\Projection;
use haddowg\JsonApiClient\Query\Query;
use haddowg\JsonApiClient\Query\ReadQuery;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * The `author` relation of a `posts` resource, hydrated.
 *
 * A marker carries no implementation. `withAuthor()` intersects it into a query's projection, which
 * is what makes `author()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.PostsResource.properties.relationships.author`
 */
interface PostHasAuthor
{
    /**
     * The related `writers` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `author`
     */
    public function author(): ?Writer;

    public ?Writer $author { get; }
}

/**
 * The `comments` relation of a `posts` resource, hydrated.
 *
 * A marker carries no implementation. `withComments()` intersects it into a query's projection,
 * which is what makes `comments()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.PostsResource.properties.relationships.comments`
 */
interface PostHasComments
{
    /**
     * @return ResourceCollection<Comment>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `comments`
     */
    public function comments(): ResourceCollection;

    /** @var ResourceCollection<Comment> */
    public ResourceCollection $comments { get; }
}

/**
 * The projection surface of `posts`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see PostProjection}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `posts` read operations
 */
trait PostProjecting
{
    /**
     * Include the `author` relation, narrowing the projection so `$post->author` type-checks.
     *
     * @return static<TProj&PostHasAuthor>
     */
    public function withAuthor(): static
    {
        return $this->including('author');
    }

    /**
     * Include the `comments` relation, narrowing the projection so `$post->comments` type-checks.
     *
     * @return static<TProj&PostHasComments>
     */
    public function withComments(): static
    {
        return $this->including('comments');
    }

    /**
     * Include paths assembled at runtime, or deeper than one hop. Nothing is narrowed, so a
     * relation is reached through its always-safe companions or after a runtime check. The runtime
     * keeps this protected; it is public here because an operation declares `include`.
     *
     * @return static
     */
    public function with(string ...$paths): static
    {
        return parent::with(...$paths);
    }

    /**
     * Sparse fieldsets, with the `posts` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[posts]` names a member `posts` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[PostBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Post::FIELDS, true)) {
                throw UnknownFieldException::for(PostBase::TYPE, $field, Post::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `posts` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.PostsResource`
 */
abstract class PostBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'posts';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    public string $title { get => $this->title(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `title`
     */
    public function title(): string
    {
        return $this->_string('title');
    }

    /**
     * The `author` linkage, without hydrating anything. Never throws.
     */
    public function authorRef(): ?Identifier
    {
        return $this->_relationship('author', true)->identifier();
    }

    /**
     * Whether the `author` linkage points at anything. Never throws.
     */
    public function hasAuthor(): bool
    {
        return !$this->_relationship('author', true)->isEmpty();
    }

    /**
     * The `comments` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function commentsRef(): array
    {
        return $this->_relationship('comments', false)->identifiers();
    }

    /**
     * Whether the `comments` linkage points at anything. Never throws.
     */
    public function hasComments(): bool
    {
        return !$this->_relationship('comments', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'author'|'comments' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, $name === 'author');
    }

    /**
     * This post's id-handle, to write through: its update, delete, relationship writes and actions,
     * exactly as `$client->posts->id(…)` offers them.
     *
     * @throws \LogicException when the post was not read through a client
     */
    public function _handle(): PostHandle
    {
        return new PostHandle($this->_fetcher(), $this->id());
    }
}

/**
 * A `posts` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `posts` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type PostIncludePath 'author'|'comments'
 * @phpstan-type PostFieldToken 'author'|'comments'|'title'
 *
 * @generated from `components.schemas.PostsResource`
 */
final class Post extends PostBase implements PostHasAuthor, PostHasComments
{
    /**
     * The members `fields[posts]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['title', 'author', 'comments'];

    public ?Writer $author { get => $this->author(); }

    /** @var ResourceCollection<Comment> */
    public ResourceCollection $comments { get => $this->comments(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `posts` one
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
     * A resource identifier for a `posts` resource that is not in hand, for a relationship write.
     *
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `author`
     */
    public function author(): ?Writer
    {
        $raw = $this->_includedOne('author', 'authorRef');

        return $raw === null ? null : Writer::_hydrate($raw, $this->_child('author'));
    }

    /**
     * @return ResourceCollection<Comment>
     *
     * @throws RelationNotIncludedException when the read did not include `comments`
     */
    public function comments(): ResourceCollection
    {
        $context = $this->_child('comments');
        $comments = [];

        foreach ($this->_includedMany('comments', 'commentsRef') as $raw) {
            $comments[] = Comment::_hydrate($raw, $context);
        }

        return new ResourceCollection($comments);
    }
}

/**
 * The `comments` relationship of one post. `related()` reads the related resources; any read the
 * server switched off is absent. No write is advertised, so none is generated.
 *
 * @generated from `/posts/{id}/comments`
 */
final class PostCommentsRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Comment, Cursor>
     *
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/posts/' . $this->id . '/comments')));
    }

    /**
     * @return PaginatedCollection<Comment, Cursor>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $comments = [];

        foreach ($context->primaryList() as $raw) {
            $comments[] = Comment::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $comments,
            Cursor::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }
}

/**
 * One post by id, before anything has been fetched. Creating the handle performs no IO; each method
 * on it is one request.
 *
 * @generated from the `posts` operations on one resource
 */
final class PostHandle
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
     * Fetch the post, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(PostProjection<PostBase>): PostProjection<TProj>)|PostProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PostBase : PostBase&TProj)
     *
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|PostProjection|null $projection = null): PostBase
    {
        $query = PostProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/posts/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The posts response', 'data');

        /** @var PostBase&TProj */
        return $context->hydrate($raw, Post::_hydrate(...));
    }

    /**
     * The `comments` relationship, a to-many the document advertises no write for.
     */
    public function comments(): PostCommentsRelation
    {
        return new PostCommentsRelation($this->fetcher, $this->id);
    }
}

/**
 * What a response carrying `posts` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /posts/{id}`
 */
final class PostProjection extends Projection
{
    /** @use PostProjecting<TProj> */
    use PostProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<PostBase>
     */
    public static function make(): self
    {
        /** @var self<PostBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<PostBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /posts`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not.
 *
 * The endpoint offers a menu of pagination strategies. `TPage` is the page a read returns: the
 * server's default, `cursor`, until a `pageByX()` picker chooses another.
 *
 * @template TProj of object
 *
 * @template TPage of Page
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type PostIncludePath from Post
 *
 * @generated from `GET /posts`
 */
final class PostQuery extends Query
{
    use QueryConditionable;

    /**
     * @param \Closure(array<string, mixed>, PageLinks): TPage $reader
     */
    public function __construct(
        private readonly Fetcher $fetcher,
        private \Closure $reader,
    ) {}

    /**
     * Include the `author` relation, narrowing the projection so `$post->author` type-checks.
     *
     * @return static<TProj&PostHasAuthor, TPage>
     */
    public function withAuthor(): static
    {
        return $this->including('author');
    }

    /**
     * Include the `comments` relation, narrowing the projection so `$post->comments` type-checks.
     *
     * @return static<TProj&PostHasComments, TPage>
     */
    public function withComments(): static
    {
        return $this->including('comments');
    }

    /**
     * Include paths assembled at runtime, or deeper than one hop. Nothing is narrowed, so a
     * relation is reached through its always-safe companions or after a runtime check. The runtime
     * keeps this protected; it is public here because an operation declares `include`.
     *
     * @return static
     */
    public function with(string ...$paths): static
    {
        return parent::with(...$paths);
    }

    /**
     * Sparse fieldsets, with the `posts` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[posts]` names a member `posts` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[PostBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Post::FIELDS, true)) {
                throw UnknownFieldException::for(PostBase::TYPE, $field, Post::FIELDS);
            }
        }

        return parent::fields($fields);
    }

    /**
     * Page by the `numbered` strategy. Sends `page[kind]=numbered` with whichever members are
     * given, replacing whatever an earlier picker set, and reads `PageNumber` pages.
     *
     * @param positive-int|null $number
     * @param positive-int|null $size
     *
     * @return self<TProj, PageNumber>
     */
    public function pageByNumbered(?int $number = null, ?int $size = null): self
    {
        return $this->choose(['kind' => 'numbered', 'number' => $number, 'size' => $size], PageNumber::read(...));
    }

    /**
     * Page by the `offset` strategy. Sends `page[kind]=offset` with whichever members are given,
     * replacing whatever an earlier picker set, and reads `Offset` pages.
     *
     * @param int<0, max>|null  $offset
     * @param positive-int|null $limit
     *
     * @return self<TProj, Offset>
     */
    public function pageByOffset(?int $offset = null, ?int $limit = null): self
    {
        return $this->choose(['kind' => 'offset', 'offset' => $offset, 'limit' => $limit], Offset::read(...));
    }

    /**
     * Page by the `cursor` strategy, the one the server uses when a read picks none. Sends
     * `page[kind]=cursor` with whichever members are given, replacing whatever an earlier picker
     * set, and reads `Cursor` pages.
     *
     * @param string|null       $after
     * @param string|null       $before
     * @param positive-int|null $size
     *
     * @return self<TProj, Cursor>
     */
    public function pageByCursor(?string $after = null, ?string $before = null, ?int $size = null): self
    {
        return $this->choose(['kind' => 'cursor', 'after' => $after, 'before' => $before, 'size' => $size], Cursor::read(...));
    }

    /**
     * Choose a strategy: replace the page parameters with its own, dropping those left null, and
     * re-type the query to read its page. The runtime hands the replaced query back as the base
     * `Query`, which is what makes narrowing it to the new page type legal.
     *
     * @template TNext of Page
     *
     * @param array<string, mixed>                              $page
     * @param \Closure(array<string, mixed>, PageLinks): TNext $reader
     *
     * @return self<TProj, TNext>
     */
    private function choose(array $page, \Closure $reader): self
    {
        /** @var self<TProj, TNext> $chosen */
        $chosen = $this->repaging(\array_filter($page, static fn(mixed $value): bool => $value !== null));
        $chosen->reader = $reader;

        return $chosen;
    }

    /**
     * Paginator parameters assembled at runtime. Public here because `GET /posts` is paginated; the
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
     * @return PaginatedCollection<TProj, TPage>
     *
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        $query = $this->toReadQuery();

        return $this->collect($this->fetcher->read($this->request($query), $query), $query);
    }

    /**
     * The first post the read matches.
     *
     * @return TProj|null
     *
     * @throws TransportException when no response is received at all
     */
    public function first(): ?object
    {
        return $this->get()->first();
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/posts'));
    }

    /**
     * @return PaginatedCollection<TProj, TPage>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $posts = [];

        foreach ($context->primaryList() as $raw) {
            $posts[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $posts,
            ($this->reader)($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Post`, but `TProj` is
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
        return $context->hydrate($raw, Post::_hydrate(...));
    }
}

/**
 * The `posts` type: every operation its collection endpoint advertises, and the way to one resource
 * by id.
 *
 * @generated from the `posts` operations
 */
final class Posts
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the posts collection, not yet sent: unprojected, unfiltered and unsorted. Until a
     * picker chooses a strategy, it reads the server's default, `cursor`.
     *
     * @return PostQuery<PostBase, Cursor>
     */
    public function query(): PostQuery
    {
        /** @var PostQuery<PostBase, Cursor> */
        return new PostQuery($this->fetcher, Cursor::read(...));
    }

    /**
     * A handle on one post by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): PostHandle
    {
        return new PostHandle($this->fetcher, $id);
    }
}
