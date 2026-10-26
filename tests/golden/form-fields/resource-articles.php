<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\FormFields;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\NotFound;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
use haddowg\JsonApiClient\Exceptions\UnknownFilterException;
use haddowg\JsonApiClient\Exceptions\UnknownSortException;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\FilePart;
use haddowg\JsonApiClient\Http\FormEncoding;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\PageLinks;
use haddowg\JsonApiClient\Pagination\PageNumber;
use haddowg\JsonApiClient\Query\Projection;
use haddowg\JsonApiClient\Query\Query;
use haddowg\JsonApiClient\Query\ReadQuery;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\Pivot;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Coerce;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\ImmutableConditionable;
use haddowg\JsonApiClient\Support\Missing;
use haddowg\JsonApiClient\Support\QueryConditionable;
use Psr\Http\Message\StreamInterface;

/**
 * The `author` relation of an `articles` resource, hydrated.
 *
 * A marker carries no implementation. `withAuthor()` intersects it into a query's projection, which
 * is what makes `author()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.ArticlesResource.properties.relationships.author`
 */
interface ArticleHasAuthor
{
    /**
     * The related `people` resource, or null when the linkage is empty.
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `author`
     */
    public function author(): ?People;

    public ?People $author { get; }
}

/**
 * The `tags` relation of an `articles` resource, hydrated.
 *
 * A marker carries no implementation. `withTags()` intersects it into a query's projection, which
 * is what makes `tags()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.ArticlesResource.properties.relationships.tags`
 */
interface ArticleHasTags
{
    /**
     * @return ResourceCollection<Tag>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `tags`
     */
    public function tags(): ResourceCollection;

    /** @var ResourceCollection<Tag> */
    public ResourceCollection $tags { get; }
}

/**
 * The projection surface of `articles`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see ArticleProjection} and {@see ArticleQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `articles` read operations
 */
trait ArticleProjecting
{
    /**
     * Include the `author` relation, narrowing the projection so `$article->author` type-checks.
     *
     * @return static<TProj&ArticleHasAuthor>
     */
    public function withAuthor(): static
    {
        return $this->including('author');
    }

    /**
     * Include the `tags` relation, narrowing the projection so `$article->tags` type-checks.
     *
     * @return static<TProj&ArticleHasTags>
     */
    public function withTags(): static
    {
        return $this->including('tags');
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
     * Sparse fieldsets, with the `articles` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[articles]` names a member `articles` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[ArticleBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Article::FIELDS, true)) {
                throw UnknownFieldException::for(ArticleBase::TYPE, $field, Article::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * An `articles` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @phpstan-import-type ArticleAddressShape from Article
 * @phpstan-import-type ArticleBlockShape from Article
 * @phpstan-import-type ArticleContactShape from Article
 *
 * @generated from `components.schemas.ArticlesResource`
 */
abstract class ArticleBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'articles';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    /** @var non-empty-string */
    public string $title { get => $this->title(); }

    public ?string $slug { get => $this->slug(); }

    public ?Status $status { get => $this->status(); }

    public ?Priority $priority { get => $this->priority(); }

    /** @var int<0, max>|null */
    public ?int $wordCount { get => $this->wordCount(); }

    public ?float $rating { get => $this->rating(); }

    public ?bool $featured { get => $this->featured(); }

    public ?\DateTimeImmutable $releasedOn { get => $this->releasedOn(); }

    public ?\DateTimeImmutable $publishedAt { get => $this->publishedAt(); }

    public ?\DateTimeImmutable $syncedAt { get => $this->syncedAt(); }

    public ?string $archivedAt { get => $this->archivedAt(); }

    public ?string $embargoLifts { get => $this->embargoLifts(); }

    public ?string $editorEmail { get => $this->editorEmail(); }

    public ?string $canonicalUrl { get => $this->canonicalUrl(); }

    public ?string $excerpt { get => $this->excerpt(); }

    /** @var positive-int|null */
    public ?int $revision { get => $this->revision(); }

    public ?string $reviewNote { get => $this->reviewNote(); }

    public ?string $originIp { get => $this->originIp(); }

    /** @var non-empty-list<string>|null */
    public ?array $keywords { get => $this->keywords(); }

    /** @var array<string, mixed>|null */
    public ?array $analytics { get => $this->analytics(); }

    /** @var ArticleAddressShape|null */
    public ?array $address { get => $this->address(); }

    public ArticleBlockHeading|ArticleBlockImage|null $block { get => $this->block(); }

    /** @var ArticleContactShape|null */
    public ?array $contact { get => $this->contact(); }

    public \DateTimeImmutable $createdAt { get => $this->createdAt(); }

    public ?string $legacyRef { get => $this->legacyRef(); }

    public int $commentCount { get => $this->commentCount(); }

    public ?string $authorName { get => $this->authorName(); }

    public ?int $score { get => $this->score(); }

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
     * Between 3 and 120 characters.
     *
     * @return non-empty-string
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `title`
     */
    public function title(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('title');

        return $value;
    }

    /**
     * Matches `^[a-z0-9]+(?:-[a-z0-9]+)*$`.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `slug`
     */
    public function slug(): ?string
    {
        return $this->_nullableString('slug');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `status`
     */
    public function status(): ?Status
    {
        return $this->_nullableEnum('status', Status::class);
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `priority`
     */
    public function priority(): ?Priority
    {
        return $this->_nullableEnum('priority', Priority::class);
    }

    /**
     * A multiple of 10.
     *
     * @return int<0, max>|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `wordCount`
     */
    public function wordCount(): ?int
    {
        /** @var int<0, max>|null $value */
        $value = $this->_nullableInt('wordCount');

        return $value;
    }

    /**
     * Greater than 0 and less than 10.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `rating`
     */
    public function rating(): ?float
    {
        return $this->_nullableFloat('rating');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `featured`
     */
    public function featured(): ?bool
    {
        return $this->_nullableBool('featured');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `releasedOn`
     */
    public function releasedOn(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('releasedOn');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `publishedAt`
     */
    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('publishedAt');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `syncedAt`
     */
    public function syncedAt(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('syncedAt');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `archivedAt`
     */
    public function archivedAt(): ?string
    {
        return $this->_nullableString('archivedAt');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `embargoLifts`
     */
    public function embargoLifts(): ?string
    {
        return $this->_nullableString('embargoLifts');
    }

    /**
     * In `email` format.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `editorEmail`
     */
    public function editorEmail(): ?string
    {
        return $this->_nullableString('editorEmail');
    }

    /**
     * In `uri` format.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `canonicalUrl`
     */
    public function canonicalUrl(): ?string
    {
        return $this->_nullableString('canonicalUrl');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `excerpt`
     */
    public function excerpt(): ?string
    {
        return $this->_nullableString('excerpt');
    }

    /**
     * @return positive-int|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `revision`
     */
    public function revision(): ?int
    {
        /** @var positive-int|null $value */
        $value = $this->_nullableInt('revision');

        return $value;
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `reviewNote`
     */
    public function reviewNote(): ?string
    {
        return $this->_nullableString('reviewNote');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `originIp`
     */
    public function originIp(): ?string
    {
        return $this->_nullableString('originIp');
    }

    /**
     * At most 10 items; no duplicate items.
     *
     * @return non-empty-list<string>|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `keywords`
     */
    public function keywords(): ?array
    {
        $raw = $this->_nullableList('keywords');

        if ($raw === null) {
            return null;
        }

        $list = [];
        foreach ($raw as $member) {
            if (!(\is_string($member))) {
                throw MalformedDocument::unexpectedMember('The "keywords" attribute of an "articles" resource', 'keywords', 'a list of string values');
            }
            $list[] = $member;
        }

        /** @var non-empty-list<string> $list */
        return $list;
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `analytics`
     */
    public function analytics(): ?array
    {
        return $this->_nullableMap('analytics');
    }

    /**
     * A nested object attribute, narrowed member by member: an optional member of the wrong type is
     * dropped rather than trusted, and a required one throws.
     *
     * @return ArticleAddressShape|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `address`
     */
    public function address(): ?array
    {
        $raw = $this->_nullableMap('address');

        if ($raw === null) {
            return null;
        }

        $shape = [];
        $street = $raw['street'] ?? null;
        $city = $raw['city'] ?? null;

        if (!\is_string($street)) {
            throw MalformedDocument::unexpectedMember('The "address" attribute of an "articles" resource', 'street', 'a string');
        }

        /** @var non-empty-string $street */
        $shape['street'] = $street;

        if ($city === null) {
            $shape['city'] = null;
        } else {
            if (!\is_string($city)) {
                throw MalformedDocument::unexpectedMember('The "address" attribute of an "articles" resource', 'city', 'a string');
            }

            $shape['city'] = $city;
        }

        return $shape;
    }

    /**
     * A tagged union, one class per branch, selected by `kind`. Narrow it with `instanceof` or
     * `match`.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `block`
     */
    public function block(): ArticleBlockHeading|ArticleBlockImage|null
    {
        $raw = $this->_nullableMap('block');

        if ($raw === null) {
            return null;
        }

        return match ($raw['kind'] ?? null) {
            ArticleBlockHeading::KIND => ArticleBlockHeading::_from($raw),
            ArticleBlockImage::KIND => ArticleBlockImage::_from($raw),
            default => throw MalformedDocument::unexpectedMember('The "block" attribute of an "articles" resource', 'kind', 'one of "heading", "image"'),
        };
    }

    /**
     * A nested object attribute, narrowed member by member: an optional member of the wrong type is
     * dropped rather than trusted, and a required one throws.
     *
     * @return ArticleContactShape|null
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `contact`
     */
    public function contact(): ?array
    {
        $raw = $this->_nullableMap('contact');

        if ($raw === null) {
            return null;
        }

        $shape = [];
        $firstName = $raw['firstName'] ?? null;
        $nickname = $raw['nickname'] ?? null;
        $phone = $raw['phone'] ?? null;
        $geo = $raw['geo'] ?? null;

        if (!\is_string($firstName)) {
            throw MalformedDocument::unexpectedMember('The "contact" attribute of an "articles" resource', 'firstName', 'a string');
        }

        $shape['firstName'] = $firstName;

        if ($nickname === null) {
            $shape['nickname'] = null;
        } else {
            if (!\is_string($nickname)) {
                throw MalformedDocument::unexpectedMember('The "contact" attribute of an "articles" resource', 'nickname', 'a string');
            }

            $shape['nickname'] = $nickname;
        }

        if ($phone === null && \array_key_exists('phone', $raw)) {
            $shape['phone'] = null;
        }

        if (\is_string($phone)) {
            $shape['phone'] = $phone;
        }

        if ($geo === null) {
            $shape['geo'] = null;
        } else {
            if (!\is_array($geo)) {
                throw MalformedDocument::unexpectedMember('The "contact" attribute of an "articles" resource', 'geo', 'an object');
            }

            $geoShape = [];
            $geoLat = $geo['lat'] ?? null;

            if ($geoLat === null) {
                $geoShape['lat'] = null;
            } else {
                if (!\is_int($geoLat)) {
                    throw MalformedDocument::unexpectedMember('The "contact" attribute of an "articles" resource', 'lat', 'an integer');
                }

                $geoShape['lat'] = $geoLat;
            }

            $shape['geo'] = $geoShape;
        }

        return $shape;
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `createdAt`
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->_dateTime('createdAt');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `legacyRef`
     */
    public function legacyRef(): ?string
    {
        return $this->_nullableString('legacyRef');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `commentCount`
     */
    public function commentCount(): int
    {
        return $this->_int('commentCount');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `authorName`
     */
    public function authorName(): ?string
    {
        return $this->_nullableString('authorName');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `score`
     */
    public function score(): ?int
    {
        return $this->_nullableInt('score');
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
     * The `tags` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function tagsRef(): array
    {
        return $this->_relationship('tags', false)->identifiers();
    }

    /**
     * Whether the `tags` linkage points at anything. Never throws.
     */
    public function hasTags(): bool
    {
        return !$this->_relationship('tags', false)->isEmpty();
    }

    /**
     * The `meta.pivot` of each `tags` linkage member, in linkage order, so the nth belongs to the
     * nth member of `tagsRef()`. Never throws: each field is checked when it is read.
     *
     * @return list<ArticleTagsPivot>
     */
    public function tagsPivots(): array
    {
        $pivots = [];

        foreach ($this->_relationship('tags', false)->identifiers() as $member) {
            $pivots[] = new ArticleTagsPivot($member);
        }

        return $pivots;
    }

    /**
     * The `publisher` linkage, without hydrating anything. Never throws.
     */
    public function publisherRef(): ?Identifier
    {
        return $this->_relationship('publisher', true)->identifier();
    }

    /**
     * Whether the `publisher` linkage points at anything. Never throws.
     */
    public function hasPublisher(): bool
    {
        return !$this->_relationship('publisher', true)->isEmpty();
    }

    /**
     * The `cover` linkage, without hydrating anything. Never throws.
     */
    public function coverRef(): ?Identifier
    {
        return $this->_relationship('cover', true)->identifier();
    }

    /**
     * Whether the `cover` linkage points at anything. Never throws.
     */
    public function hasCover(): bool
    {
        return !$this->_relationship('cover', true)->isEmpty();
    }

    /**
     * The `locked` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function lockedRef(): array
    {
        return $this->_relationship('locked', false)->identifiers();
    }

    /**
     * Whether the `locked` linkage points at anything. Never throws.
     */
    public function hasLocked(): bool
    {
        return !$this->_relationship('locked', false)->isEmpty();
    }

    /**
     * The `attachments` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function attachmentsRef(): array
    {
        return $this->_relationship('attachments', false)->identifiers();
    }

    /**
     * Whether the `attachments` linkage points at anything. Never throws.
     */
    public function hasAttachments(): bool
    {
        return !$this->_relationship('attachments', false)->isEmpty();
    }

    /**
     * The `editor` linkage, without hydrating anything. Never throws.
     */
    public function editorRef(): ?Identifier
    {
        return $this->_relationship('editor', true)->identifier();
    }

    /**
     * Whether the `editor` linkage points at anything. Never throws.
     */
    public function hasEditor(): bool
    {
        return !$this->_relationship('editor', true)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'author'|'tags'|'publisher'|'cover'|'locked'|'attachments'|'editor' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, \in_array($name, ['author', 'publisher', 'cover', 'editor'], true));
    }
}

/**
 * An `articles` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `articles` surface accepts, and the descriptor constants
 * the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class
 * using one imports it from here.
 *
 * @phpstan-type ArticleAddressShape array{street: non-empty-string, city: string|null}
 * @phpstan-type ArticleBlockShape array{text: non-empty-string, kind: 'heading'}|array{src: string|null, kind: 'image'}
 * @phpstan-type ArticleContactShape array{firstName: string, nickname: string|null, phone?: string|null, geo: array{lat: int|null}|null}
 * @phpstan-type ArticleStatusInput Status|'archived'|'draft'|'published'
 * @phpstan-type ArticlePriorityInput Priority|1|2
 * @phpstan-type ArticleDateInput \DateTimeImmutable|string
 * @phpstan-type ArticleAuthorInput Identifier|People|null
 * @phpstan-type ArticleTagsInput list<Identifier|Tag>
 * @phpstan-type ArticlePublisherInput Identifier|People|null
 * @phpstan-type ArticleCoverInput Identifier|Image|null
 * @phpstan-type ArticleLockedInput list<Identifier|People>
 * @phpstan-type ArticleAttachmentsInput list<Identifier|Image|Video>
 * @phpstan-type ArticleReviewerInput Identifier|People|null
 * @phpstan-type ArticleWatchersInput list<Identifier|People>
 * @phpstan-type ArticleEditorInput Identifier|People|null
 * @phpstan-type ArticleCreateShape array{title: non-empty-string, slug?: string, status?: ArticleStatusInput, priority?: ArticlePriorityInput, wordCount?: int<0, max>|null, rating?: float, featured?: bool, releasedOn?: ArticleDateInput, publishedAt?: ArticleDateInput|null, syncedAt?: ArticleDateInput, archivedAt?: string, embargoLifts?: string, editorEmail?: string, canonicalUrl?: string, excerpt?: string, revision?: positive-int, reviewNote?: string|null, originIp?: string, keywords?: non-empty-list<string>, analytics?: array<string, mixed>|null, address?: array{street: non-empty-string, city?: string}, block?: array{text: non-empty-string, kind: string}|array{src?: string, kind: string}, contact?: array{firstName?: string, nickname?: string, pin?: string, phone?: string, geo?: array{lat?: int}}|null, authorName?: string, score?: int, author?: ArticleAuthorInput, tags?: ArticleTagsInput, publisher?: ArticlePublisherInput, cover?: ArticleCoverInput, locked?: ArticleLockedInput, attachments?: ArticleAttachmentsInput, editor?: ArticleEditorInput, reviewer?: ArticleReviewerInput, watchers?: ArticleWatchersInput}
 * @phpstan-type ArticleUpdateShape array{title?: non-empty-string, slug?: string, status?: ArticleStatusInput, priority?: ArticlePriorityInput, wordCount?: int<0, max>|null, rating?: float, featured?: bool, releasedOn?: ArticleDateInput, publishedAt?: ArticleDateInput|null, syncedAt?: ArticleDateInput, archivedAt?: string, embargoLifts?: string, editorEmail?: string, canonicalUrl?: string, excerpt?: string, reviewNote?: string|null, originIp?: string, keywords?: non-empty-list<string>, analytics?: array<string, mixed>|null, address?: array{street?: non-empty-string, city?: string}, block?: array{text?: non-empty-string, kind?: string}|array{src?: string, kind?: string}, contact?: array{firstName?: string, nickname?: string, pin?: string, phone?: string, geo?: array{lat?: int}}|null, authorName?: string, score?: int, author?: ArticleAuthorInput, tags?: ArticleTagsInput, publisher?: ArticlePublisherInput, cover?: ArticleCoverInput, attachments?: ArticleAttachmentsInput, editor?: ArticleEditorInput, reviewer?: ArticleReviewerInput, watchers?: ArticleWatchersInput}
 * @phpstan-type ArticleRangeShape array{min?: float, max?: float}
 * @phpstan-type ArticleDateRangeShape array{min?: ArticleDateInput, max?: ArticleDateInput}
 * @phpstan-type ArticleFilterShape array{status?: string, wordCount?: int, archived?: string, rating?: ArticleRangeShape, publishedAt?: ArticleDateRangeShape, labels?: list<string>, search?: string, urgent?: string, featured?: bool, releasedOn?: string, keywords?: list<string>, 'author.name'?: string, unpublished?: string}
 * @phpstan-type ArticleSortToken '-title'|'-wordCount'|'title'|'wordCount'
 * @phpstan-type ArticleIncludePath 'author'|'author.company'|'tags'
 * @phpstan-type ArticleFieldToken 'address'|'analytics'|'archivedAt'|'attachments'|'author'|'authorName'|'block'|'canonicalUrl'|'commentCount'|'contact'|'cover'|'createdAt'|'editor'|'editorEmail'|'embargoLifts'|'excerpt'|'featured'|'keywords'|'legacyRef'|'locked'|'originIp'|'priority'|'publishedAt'|'publisher'|'rating'|'releasedOn'|'reviewNote'|'revision'|'score'|'slug'|'status'|'syncedAt'|'tags'|'title'|'wordCount'
 * @phpstan-type ArticleCountToken '_self_'|'tags'
 *
 * @generated from `components.schemas.ArticlesResource`
 */
final class Article extends ArticleBase implements ArticleHasAuthor, ArticleHasTags
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = [
        'title',
        'slug',
        'status',
        'priority',
        'wordCount',
        'rating',
        'featured',
        'releasedOn',
        'publishedAt',
        'syncedAt',
        'archivedAt',
        'embargoLifts',
        'editorEmail',
        'canonicalUrl',
        'excerpt',
        'revision',
        'reviewNote',
        'originIp',
        'keywords',
        'analytics',
        'address',
        'block',
        'contact',
        'authorName',
        'score',
        'author',
        'tags',
        'publisher',
        'cover',
        'locked',
        'attachments',
        'editor',
        'reviewer',
        'watchers',
    ];

    /**
     * The members the update document accepts, where they differ from `WRITABLE`.
     *
     * @var list<string>
     */
    public const array WRITABLE_ON_UPDATE = [
        'title',
        'slug',
        'status',
        'priority',
        'wordCount',
        'rating',
        'featured',
        'releasedOn',
        'publishedAt',
        'syncedAt',
        'archivedAt',
        'embargoLifts',
        'editorEmail',
        'canonicalUrl',
        'excerpt',
        'reviewNote',
        'originIp',
        'keywords',
        'analytics',
        'address',
        'block',
        'contact',
        'authorName',
        'score',
        'author',
        'tags',
        'publisher',
        'cover',
        'attachments',
        'editor',
        'reviewer',
        'watchers',
    ];

    /**
     * Members a create document requires. Read-only attributes (`createdAt`, `legacyRef`,
     * `commentCount`) are in no write shape.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['title'];

    /**
     * The filters `GET /articles` accepts. The runtime guard on both array doors checks names
     * against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array FILTERS = [
        'status',
        'wordCount',
        'archived',
        'rating',
        'publishedAt',
        'labels',
        'search',
        'urgent',
        'featured',
        'releasedOn',
        'keywords',
        'author.name',
        'unpublished',
    ];

    /**
     * The members a structured filter's own value accepts. An array shape accepts an unknown key
     * one level down just as it does at the top, so these are checked at runtime too.
     *
     * @var array<string, list<string>>
     */
    public const array FILTER_MEMBERS = [
        'rating' => ['min', 'max'],
        'publishedAt' => ['min', 'max'],
    ];

    /**
     * The signed sort tokens `GET /articles` accepts, both directions of each.
     *
     * @var list<string>
     */
    public const array SORT_TOKENS = ['title', '-title', 'wordCount', '-wordCount'];

    /**
     * The members `fields[articles]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = [
        'title',
        'slug',
        'status',
        'priority',
        'wordCount',
        'rating',
        'featured',
        'releasedOn',
        'publishedAt',
        'syncedAt',
        'archivedAt',
        'embargoLifts',
        'editorEmail',
        'canonicalUrl',
        'excerpt',
        'revision',
        'reviewNote',
        'originIp',
        'keywords',
        'analytics',
        'address',
        'block',
        'contact',
        'createdAt',
        'legacyRef',
        'commentCount',
        'authorName',
        'score',
        'author',
        'tags',
        'publisher',
        'cover',
        'locked',
        'attachments',
        'editor',
    ];

    /**
     * The `withCount` tokens `GET /articles` advertises.
     *
     * @var list<string>
     */
    public const array COUNT_TOKENS = ['_self_', 'tags'];

    /**
     * The Countable profile, which the client adds to `Accept` only when a request uses
     * `withCount`.
     */
    public const string COUNTABLE_PROFILE = 'https://haddowg.github.io/json-api/profiles/countable/';

    public ?People $author { get => $this->author(); }

    /** @var ResourceCollection<Tag> */
    public ResourceCollection $tags { get => $this->tags(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `articles` one
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
     * A resource identifier for an `articles` resource that is not in hand, for a relationship
     * write.
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
     * @return ArticleProjection<ArticleBase>
     */
    public static function projection(): ArticleProjection
    {
        return ArticleProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param ArticleCreateShape|null $input
     *
     * @return ($input is null ? ArticleCreateBuilder : ArticleCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `articles` does not have
     */
    public static function create(?array $input = null): ArticleCreateBuilder|ArticleCreate
    {
        return $input === null ? new ArticleCreateBuilder() : ArticleCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param ArticleUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `articles` does not have
     */
    public static function update(?array $input = null): ArticleUpdate
    {
        return $input === null ? new ArticleUpdate() : ArticleUpdate::from($input);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `author`
     */
    public function author(): ?People
    {
        $raw = $this->_includedOne('author', 'authorRef');

        return $raw === null ? null : People::_hydrate($raw, $this->_child('author'));
    }

    /**
     * @return ResourceCollection<Tag>
     *
     * @throws RelationNotIncludedException when the read did not include `tags`
     */
    public function tags(): ResourceCollection
    {
        $context = $this->_child('tags');
        $tags = [];

        foreach ($this->_includedMany('tags', 'tagsRef') as $raw) {
            $tags[] = Tag::_hydrate($raw, $context);
        }

        return new ResourceCollection($tags);
    }
}

/**
 * The resource-scoped custom actions of `articles`, reached through one article's handle. Each
 * signature is derived from the action's own request body and response.
 *
 * @phpstan-import-type ArticleCreateShape from Article
 *
 * @generated from `GET /articles/{id}/-actions/cover` and `POST /articles/{id}/-actions/draft` and `POST /articles/{id}/-actions/publish`
 */
final class ArticleActions
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * Cover: `GET /articles/{id}/-actions/cover`. It declares no request body, so it takes no
     * input.
     *
     * It answers as `image/png`. An error is a JSON:API document.
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function cover(): StreamInterface
    {
        $request = Request::get('/articles/' . $this->id . '/-actions/cover');

        return $this->fetcher->stream($request->withAccept('image/png'));
    }

    /**
     * Draft: `POST /articles/{id}/-actions/draft`. The body is a create document of `articles`, in
     * any of its three input forms.
     *
     * Save a draft revision.
     *
     * @param ArticleCreate|ArticleCreateBuilder|ArticleCreateShape $input
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function draft(ArticleCreate|ArticleCreateBuilder|array $input): void
    {
        $document = match (true) {
            $input instanceof ArticleCreate => $input,
            $input instanceof ArticleCreateBuilder => $input->build(),
            default => ArticleCreate::from($input),
        };

        $this->fetcher->send(Request::post(
            '/articles/' . $this->id . '/-actions/draft',
            \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Publish the article: `POST /articles/{id}/-actions/publish`. It declares no request body, so
     * it takes no input.
     *
     * @template TProj of object
     *
     * @param (callable(ArticleProjection<ArticleBase>): ArticleProjection<TProj>)|ArticleProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ArticleBase : ArticleBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function publish(callable|ArticleProjection|null $projection = null): ArticleBase
    {
        $query = ArticleProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/articles/' . $this->id . '/-actions/publish')),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The publish response', 'data');

        /** @var ArticleBase&TProj */
        return $context->hydrate($raw, Article::_hydrate(...));
    }
}

/**
 * The `attachments` relationship of one article. `get()` reads the linkage and `related()` reads
 * the related resources; any read the server switched off is absent. The writes are `add()`,
 * `remove()` and `replace()`, the methods the relationship endpoint declares, and no others.
 *
 * @generated from `/articles/{id}/attachments` and `/articles/{id}/relationships/attachments`
 */
final class ArticleAttachmentsRelation
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
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": [
     *             {
     *                 "type": "images",
     *                 "id": "img-1"
     *             }
     *         ],
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/articles/art-1/relationships/attachments",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1attachments/get"
     *             },
     *             "related": {
     *                 "href": "https://api.example.com/articles/art-1/attachments",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1attachments/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1attachments/get"
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
            $this->fetcher->read(Request::get('/articles/' . $this->id . '/relationships/attachments')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Image|Video, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/articles/' . $this->id . '/attachments')));
    }

    /**
     * Add members to the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Image|Video> $attachments
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
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
     *                 "type": "images",
     *                 "id": "img-1"
     *             }
     *         ]
     *     }
     */
    public function add(array $attachments): void
    {
        $this->fetcher->send(Request::post(
            '/articles/' . $this->id . '/relationships/attachments',
            \json_encode(['data' => Coerce::linkageList($attachments)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove members from the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Image|Video> $attachments
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
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
     *                 "type": "images",
     *                 "id": "img-1"
     *             }
     *         ]
     *     }
     */
    public function remove(array $attachments): void
    {
        $this->fetcher->send(Request::delete(
            '/articles/' . $this->id . '/relationships/attachments',
            \json_encode(['data' => Coerce::linkageList($attachments)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Replace every member of the relationship.
     *
     * @param list<Identifier|Image|Video> $attachments
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
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
     *                 "type": "images",
     *                 "id": "img-1"
     *             }
     *         ]
     *     }
     */
    public function replace(array $attachments): void
    {
        $this->fetcher->send(Request::patch(
            '/articles/' . $this->id . '/relationships/attachments',
            \json_encode(['data' => Coerce::linkageList($attachments)], \JSON_THROW_ON_ERROR),
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
            $identifiers[] = Identifier::fromArray($raw, 'the "attachments" linkage');
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
     * @return PaginatedCollection<Image|Video, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $attachments = [];

        foreach ($context->primaryList() as $raw) {
            $attachments[] = self::member($raw, $context);
        }

        return new PaginatedCollection(
            $attachments,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }

    /**
     * The related resource as the class its own `type` names.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource is of a type the relation does not declare
     */
    private static function member(array $raw, ResourceContext $context): Image|Video
    {
        return match ($raw['type'] ?? null) {
            'images' => Image::_hydrate($raw, $context),
            'videos' => Video::_hydrate($raw, $context),
            default => throw MalformedDocument::unexpectedMember('The related resource', 'type', 'one of "images", "videos"'),
        };
    }
}

/**
 * The `author` relationship of one article. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/articles/{id}/author` and `/articles/{id}/relationships/author`
 */
final class ArticleAuthorRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone: the identifier of the related resource, or null, without fetching it.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "people",
     *             "id": "per-1"
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/articles/art-1/relationships/author",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1author/get"
     *             },
     *             "related": {
     *                 "href": "https://api.example.com/articles/art-1/author",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1author/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1author/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/articles/' . $this->id . '/relationships/author'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "author" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): ?People
    {
        $context = $this->fetcher->read(Request::get('/articles/' . $this->id . '/author'));
        $raw = $context->primary();

        return $raw === null ? null : People::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "people",
     *             "id": "per-1"
     *         }
     *     }
     */
    public function set(Identifier|People|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/articles/' . $this->id . '/relationships/author',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * The `heading` branch of the `block` attribute of an `articles` resource, selected by `kind`.
 *
 * @generated from `components.schemas.ArticlesResource`, the `heading` branch of `block`
 */
final class ArticleBlockHeading
{
    /**
     * The `kind` value that selects this branch.
     */
    public const string KIND = 'heading';

    /**
     * @param non-empty-string $text
     */
    public function __construct(
        public readonly string $text,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $text = $raw['text'] ?? null;

        $textValue = null;
        if (!\is_string($text)) {
            throw MalformedDocument::unexpectedMember('The "block" attribute of an "articles" resource', 'text', 'a string');
        }

        /** @var non-empty-string $text */
        $textValue = $text;

        return new self($textValue);
    }
}

/**
 * The `image` branch of the `block` attribute of an `articles` resource, selected by `kind`.
 *
 * @generated from `components.schemas.ArticlesResource`, the `image` branch of `block`
 */
final class ArticleBlockImage
{
    /**
     * The `kind` value that selects this branch.
     */
    public const string KIND = 'image';

    public function __construct(
        public readonly ?string $src,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $src = $raw['src'] ?? null;

        $srcValue = null;
        if ($src === null) {
            $srcValue = null;
        } else {
            if (!\is_string($src)) {
                throw MalformedDocument::unexpectedMember('The "block" attribute of an "articles" resource', 'src', 'a string');
            }

            $srcValue = $src;
        }

        return new self($srcValue);
    }
}

/**
 * The collection-scoped custom actions of `articles`, reached through the type accessor. Each
 * signature is derived from the action's own request body and response.
 *
 * @generated from `POST /articles/-actions/bulk-import` and `POST /articles/-actions/import` and `GET /articles/-actions/report` and `GET /articles/-actions/stats`
 */
final class ArticleCollectionActions
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Bulk import: `POST /articles/-actions/bulk-import`. The body goes over the wire as form
     * fields for a form media type, JSON-encoded for a JSON one and verbatim otherwise, as
     * `$contentType`, which is one of `text/csv`, `image/*`, `multipart/form-data`,
     * `application/json` or `application/x-www-form-urlencoded`. The body is optional, so it may be
     * left out.
     *
     * @template TContentType of 'text/csv'|'multipart/form-data'|'application/json'|'application/x-www-form-urlencoded'|non-empty-string
     *
     * @param TContentType                                                                                                                                                                                                                                                                                                                                                                                               $contentType
     * @param (TContentType is 'text/csv' ? StreamInterface|string : (TContentType is 'multipart/form-data' ? array{title: string, cover?: FilePart, tags?: list<string>, rows?: int, status?: 'draft'|'published'} : (TContentType is 'application/json' ? list<array{title: string}> : (TContentType is 'application/x-www-form-urlencoded' ? array{title: string, ...<string, int>} : StreamInterface|string))))|null $body
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when the endpoint does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function bulkImport(
        string $contentType,
        StreamInterface|string|array|null $body = null,
    ): void {
        if (!\in_array($contentType, ['text/csv', 'multipart/form-data', 'application/json', 'application/x-www-form-urlencoded'], true) && !\str_starts_with($contentType, 'image/')) {
            throw new \InvalidArgumentException(\sprintf('The "bulk-import" action takes its body as one of text/csv, image/*, multipart/form-data, application/json, application/x-www-form-urlencoded, not %s.', $contentType));
        }

        $request = Request::post('/articles/-actions/bulk-import');

        $request = match (true) {
            $body === null => $request,
            $contentType === 'multipart/form-data' && \is_array($body) => $request->withMultipartBody(FormEncoding::multipartFields($body)),
            $contentType === 'application/x-www-form-urlencoded' && \is_array($body) => $request->withFormBody(FormEncoding::formFields($body)),
            $contentType === 'application/json' => $request->withRawBody(\json_encode($body, \JSON_THROW_ON_ERROR), $contentType),
            $body instanceof StreamInterface, \is_string($body) => $request->withRawBody($body, $contentType),
            default => throw new \InvalidArgumentException(\sprintf('The "bulk-import" action cannot send this body as %s.', $contentType)),
        };

        $this->fetcher->send($request);
    }

    /**
     * Import: `POST /articles/-actions/import`. The body goes over the wire verbatim as
     * `application/octet-stream`, while the response is still negotiated as JSON:API. The body is
     * optional, so it may be left out.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the action is not permitted
     * @throws NotFound           when the endpoint does not exist
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ValidationFailed   when the payload fails validation
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function import(StreamInterface|string|null $body = null): void
    {
        $request = Request::post('/articles/-actions/import');

        $this->fetcher->send(
            $body === null ? $request : $request->withRawBody($body, 'application/octet-stream'),
        );
    }

    /**
     * Report: `GET /articles/-actions/report`. It declares no request body, so it takes no input.
     *
     * It answers with JSON:API unless `$accept` asks for `text/csv`, `application/pdf` or
     * `application/json` instead. An error is always a JSON:API document.
     *
     * @param 'application/vnd.api+json'|'text/csv'|'application/pdf'|'application/json' $accept the media type to ask for; JSON:API unless another is named
     *
     * @return ($accept is 'text/csv' ? string : ($accept is 'application/pdf' ? StreamInterface : ($accept is 'application/json' ? ArticleSummary : array<string, mixed>)))
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when the endpoint does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when a JSON body is not what its schema says
     */
    public function report(string $accept = 'application/vnd.api+json'): array|string|StreamInterface|ArticleSummary
    {
        $request = Request::get('/articles/-actions/report');

        if ($accept === 'text/csv') {
            return $this->fetcher->text($request->withAccept('text/csv'));
        }

        if ($accept === 'application/pdf') {
            return $this->fetcher->stream($request->withAccept('application/pdf'));
        }

        if ($accept === 'application/json') {
            return ArticleSummary::_from($this->fetcher->jsonObject($request->withAccept('application/json')));
        }

        return $this->fetcher->meta($request);
    }

    /**
     * Stats: `GET /articles/-actions/stats`. It declares no request body, so it takes no input.
     *
     * @return array<string, mixed>
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the action is not permitted
     * @throws NotFound             when the endpoint does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the payload fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "meta": {
     *             "published": 12
     *         }
     *     }
     */
    public function stats(): array
    {
        return $this->fetcher->meta(Request::get('/articles/-actions/stats'));
    }
}

/**
 * The `cover` relationship of one article. `get()` reads the linkage; any read the server switched
 * off is absent. The writes are `set()`, the methods the relationship endpoint declares, and no
 * others.
 *
 * @generated from `/articles/{id}/relationships/cover`
 */
final class ArticleCoverRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone: the identifier of the related resource, or null, without fetching it.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/articles/' . $this->id . '/relationships/cover'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "cover" linkage');
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "images",
     *             "id": "img-1"
     *         }
     *     }
     */
    public function set(Identifier|Image|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/articles/' . $this->id . '/relationships/cover',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * The canonical create input for `articles`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type ArticleCreateShape from Article
 *
 * @generated from `POST /articles`
 */
final class ArticleCreate
{
    /**
     * @param non-empty-string $title
     * @param int<0, max>|Missing|null $wordCount
     * @param positive-int|Missing $revision
     * @param non-empty-list<string>|Missing $keywords
     * @param array<string, mixed>|Missing|null $analytics
     * @param array{street: non-empty-string, city?: string}|Missing $address
     * @param array{text: non-empty-string, kind: string}|array{src?: string, kind: string}|Missing $block
     * @param array{firstName?: string, nickname?: string, pin?: string, phone?: string, geo?: array{lat?: int}}|Missing|null $contact
     * @param Identifier|People|Missing|null $author
     * @param list<Identifier|Tag>|Missing $tags
     * @param Identifier|People|Missing|null $publisher
     * @param Identifier|Image|Missing|null $cover
     * @param list<Identifier|People>|Missing $locked
     * @param list<Identifier|Image|Video>|Missing $attachments
     * @param Identifier|People|Missing|null $reviewer
     * @param list<Identifier|People>|Missing $watchers
     * @param Identifier|People|Missing|null $editor
     */
    public function __construct(
        public readonly string $title,
        public readonly string|Missing $slug = Missing::Value,
        public readonly Status|Missing $status = Missing::Value,
        public readonly Priority|Missing $priority = Missing::Value,
        public readonly int|Missing|null $wordCount = Missing::Value,
        public readonly float|Missing $rating = Missing::Value,
        public readonly bool|Missing $featured = Missing::Value,
        public readonly \DateTimeImmutable|Missing $releasedOn = Missing::Value,
        public readonly \DateTimeImmutable|Missing|null $publishedAt = Missing::Value,
        public readonly \DateTimeImmutable|Missing $syncedAt = Missing::Value,
        public readonly string|Missing $archivedAt = Missing::Value,
        public readonly string|Missing $embargoLifts = Missing::Value,
        public readonly string|Missing $editorEmail = Missing::Value,
        public readonly string|Missing $canonicalUrl = Missing::Value,
        public readonly string|Missing $excerpt = Missing::Value,
        public readonly int|Missing $revision = Missing::Value,
        public readonly string|Missing|null $reviewNote = Missing::Value,
        public readonly string|Missing $originIp = Missing::Value,
        public readonly array|Missing $keywords = Missing::Value,
        public readonly array|Missing|null $analytics = Missing::Value,
        public readonly array|Missing $address = Missing::Value,
        public readonly array|Missing $block = Missing::Value,
        public readonly array|Missing|null $contact = Missing::Value,
        public readonly string|Missing $authorName = Missing::Value,
        public readonly int|Missing $score = Missing::Value,
        public readonly Identifier|People|Missing|null $author = Missing::Value,
        public readonly array|Missing $tags = Missing::Value,
        public readonly Identifier|People|Missing|null $publisher = Missing::Value,
        public readonly Identifier|Image|Missing|null $cover = Missing::Value,
        public readonly array|Missing $locked = Missing::Value,
        public readonly array|Missing $attachments = Missing::Value,
        public readonly Identifier|People|Missing|null $reviewer = Missing::Value,
        public readonly array|Missing $watchers = Missing::Value,
        public readonly Identifier|People|Missing|null $editor = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param ArticleCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `articles`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Article::WRITABLE, true)) {
                throw UnknownAttributeException::for(ArticleBase::TYPE, (string) $member, Article::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            title: $input['title'],
            slug: \array_key_exists('slug', $input) ? $input['slug'] : Missing::Value,
            status: Coerce::enum(Status::class, \array_key_exists('status', $input) ? $input['status'] : Missing::Value),
            priority: Coerce::enum(Priority::class, \array_key_exists('priority', $input) ? $input['priority'] : Missing::Value),
            wordCount: \array_key_exists('wordCount', $input) ? $input['wordCount'] : Missing::Value,
            rating: \array_key_exists('rating', $input) ? $input['rating'] : Missing::Value,
            featured: \array_key_exists('featured', $input) ? $input['featured'] : Missing::Value,
            releasedOn: Coerce::dateTime(\array_key_exists('releasedOn', $input) ? $input['releasedOn'] : Missing::Value),
            publishedAt: Coerce::nullableDateTime(\array_key_exists('publishedAt', $input) ? $input['publishedAt'] : Missing::Value),
            syncedAt: Coerce::dateTime(\array_key_exists('syncedAt', $input) ? $input['syncedAt'] : Missing::Value),
            archivedAt: \array_key_exists('archivedAt', $input) ? $input['archivedAt'] : Missing::Value,
            embargoLifts: \array_key_exists('embargoLifts', $input) ? $input['embargoLifts'] : Missing::Value,
            editorEmail: \array_key_exists('editorEmail', $input) ? $input['editorEmail'] : Missing::Value,
            canonicalUrl: \array_key_exists('canonicalUrl', $input) ? $input['canonicalUrl'] : Missing::Value,
            excerpt: \array_key_exists('excerpt', $input) ? $input['excerpt'] : Missing::Value,
            revision: \array_key_exists('revision', $input) ? $input['revision'] : Missing::Value,
            reviewNote: \array_key_exists('reviewNote', $input) ? $input['reviewNote'] : Missing::Value,
            originIp: \array_key_exists('originIp', $input) ? $input['originIp'] : Missing::Value,
            keywords: \array_key_exists('keywords', $input) ? $input['keywords'] : Missing::Value,
            analytics: \array_key_exists('analytics', $input) ? $input['analytics'] : Missing::Value,
            address: \array_key_exists('address', $input) ? $input['address'] : Missing::Value,
            block: \array_key_exists('block', $input) ? $input['block'] : Missing::Value,
            contact: \array_key_exists('contact', $input) ? $input['contact'] : Missing::Value,
            authorName: \array_key_exists('authorName', $input) ? $input['authorName'] : Missing::Value,
            score: \array_key_exists('score', $input) ? $input['score'] : Missing::Value,
            author: \array_key_exists('author', $input) ? $input['author'] : Missing::Value,
            tags: \array_key_exists('tags', $input) ? $input['tags'] : Missing::Value,
            publisher: \array_key_exists('publisher', $input) ? $input['publisher'] : Missing::Value,
            cover: \array_key_exists('cover', $input) ? $input['cover'] : Missing::Value,
            locked: \array_key_exists('locked', $input) ? $input['locked'] : Missing::Value,
            attachments: \array_key_exists('attachments', $input) ? $input['attachments'] : Missing::Value,
            reviewer: \array_key_exists('reviewer', $input) ? $input['reviewer'] : Missing::Value,
            watchers: \array_key_exists('watchers', $input) ? $input['watchers'] : Missing::Value,
            editor: \array_key_exists('editor', $input) ? $input['editor'] : Missing::Value,
        );
    }

    /**
     * The `POST /articles` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => ArticleBase::TYPE];

        $attributes = ['title' => $this->title];

        if (!$this->slug instanceof Missing) {
            $attributes['slug'] = $this->slug;
        }

        if (!$this->status instanceof Missing) {
            $attributes['status'] = $this->status->value;
        }

        if (!$this->priority instanceof Missing) {
            $attributes['priority'] = $this->priority->value;
        }

        if (!$this->wordCount instanceof Missing) {
            $attributes['wordCount'] = $this->wordCount;
        }

        if (!$this->rating instanceof Missing) {
            $attributes['rating'] = $this->rating;
        }

        if (!$this->featured instanceof Missing) {
            $attributes['featured'] = $this->featured;
        }

        if (!$this->releasedOn instanceof Missing) {
            $attributes['releasedOn'] = $this->releasedOn->format('Y-m-d');
        }

        if (!$this->publishedAt instanceof Missing) {
            $attributes['publishedAt'] = $this->publishedAt?->format(\DateTimeInterface::ATOM);
        }

        if (!$this->syncedAt instanceof Missing) {
            $attributes['syncedAt'] = $this->syncedAt->format(\DateTimeInterface::ATOM);
        }

        if (!$this->archivedAt instanceof Missing) {
            $attributes['archivedAt'] = $this->archivedAt;
        }

        if (!$this->embargoLifts instanceof Missing) {
            $attributes['embargoLifts'] = $this->embargoLifts;
        }

        if (!$this->editorEmail instanceof Missing) {
            $attributes['editorEmail'] = $this->editorEmail;
        }

        if (!$this->canonicalUrl instanceof Missing) {
            $attributes['canonicalUrl'] = $this->canonicalUrl;
        }

        if (!$this->excerpt instanceof Missing) {
            $attributes['excerpt'] = $this->excerpt;
        }

        if (!$this->revision instanceof Missing) {
            $attributes['revision'] = $this->revision;
        }

        if (!$this->reviewNote instanceof Missing) {
            $attributes['reviewNote'] = $this->reviewNote;
        }

        if (!$this->originIp instanceof Missing) {
            $attributes['originIp'] = $this->originIp;
        }

        if (!$this->keywords instanceof Missing) {
            $attributes['keywords'] = $this->keywords;
        }

        if (!$this->analytics instanceof Missing) {
            $attributes['analytics'] = $this->analytics;
        }

        if (!$this->address instanceof Missing) {
            $attributes['address'] = $this->address;
        }

        if (!$this->block instanceof Missing) {
            $attributes['block'] = $this->block;
        }

        if (!$this->contact instanceof Missing) {
            $attributes['contact'] = $this->contact;
        }

        if (!$this->authorName instanceof Missing) {
            $attributes['authorName'] = $this->authorName;
        }

        if (!$this->score instanceof Missing) {
            $attributes['score'] = $this->score;
        }

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->author instanceof Missing) {
            $relationships['author'] = ['data' => Coerce::linkage($this->author)];
        }

        if (!$this->tags instanceof Missing) {
            $relationships['tags'] = ['data' => Coerce::linkageList($this->tags)];
        }

        if (!$this->publisher instanceof Missing) {
            $relationships['publisher'] = ['data' => Coerce::linkage($this->publisher)];
        }

        if (!$this->cover instanceof Missing) {
            $relationships['cover'] = ['data' => Coerce::linkage($this->cover)];
        }

        if (!$this->locked instanceof Missing) {
            $relationships['locked'] = ['data' => Coerce::linkageList($this->locked)];
        }

        if (!$this->attachments instanceof Missing) {
            $relationships['attachments'] = ['data' => Coerce::linkageList($this->attachments)];
        }

        if (!$this->reviewer instanceof Missing) {
            $relationships['reviewer'] = ['data' => Coerce::linkage($this->reviewer)];
        }

        if (!$this->watchers instanceof Missing) {
            $relationships['watchers'] = ['data' => Coerce::linkageList($this->watchers)];
        }

        if (!$this->editor instanceof Missing) {
            $relationships['editor'] = ['data' => Coerce::linkage($this->editor)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `articles`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /articles`
 */
final class ArticleCreateBuilder
{
    use Conditionable;

    /**
     * @var non-empty-string|Missing
     */
    private string|Missing $title = Missing::Value;

    private string|Missing $slug = Missing::Value;

    private Status|Missing $status = Missing::Value;

    private Priority|Missing $priority = Missing::Value;

    /**
     * @var int<0, max>|Missing|null
     */
    private int|Missing|null $wordCount = Missing::Value;

    private float|Missing $rating = Missing::Value;

    private bool|Missing $featured = Missing::Value;

    private \DateTimeImmutable|Missing $releasedOn = Missing::Value;

    private \DateTimeImmutable|Missing|null $publishedAt = Missing::Value;

    private \DateTimeImmutable|Missing $syncedAt = Missing::Value;

    private string|Missing $archivedAt = Missing::Value;

    private string|Missing $embargoLifts = Missing::Value;

    private string|Missing $editorEmail = Missing::Value;

    private string|Missing $canonicalUrl = Missing::Value;

    private string|Missing $excerpt = Missing::Value;

    /**
     * @var positive-int|Missing
     */
    private int|Missing $revision = Missing::Value;

    private string|Missing|null $reviewNote = Missing::Value;

    private string|Missing $originIp = Missing::Value;

    /**
     * @var non-empty-list<string>|Missing
     */
    private array|Missing $keywords = Missing::Value;

    /**
     * @var array<string, mixed>|Missing|null
     */
    private array|Missing|null $analytics = Missing::Value;

    /**
     * @var array{street: non-empty-string, city?: string}|Missing
     */
    private array|Missing $address = Missing::Value;

    /**
     * @var array{text: non-empty-string, kind: string}|array{src?: string, kind: string}|Missing
     */
    private array|Missing $block = Missing::Value;

    /**
     * @var array{firstName?: string, nickname?: string, pin?: string, phone?: string, geo?: array{lat?: int}}|Missing|null
     */
    private array|Missing|null $contact = Missing::Value;

    private string|Missing $authorName = Missing::Value;

    private int|Missing $score = Missing::Value;

    private Identifier|People|Missing|null $author = Missing::Value;

    /**
     * @var list<Identifier|Tag>|Missing
     */
    private array|Missing $tags = Missing::Value;

    private Identifier|People|Missing|null $publisher = Missing::Value;

    private Identifier|Image|Missing|null $cover = Missing::Value;

    /**
     * @var list<Identifier|People>|Missing
     */
    private array|Missing $locked = Missing::Value;

    /**
     * @var list<Identifier|Image|Video>|Missing
     */
    private array|Missing $attachments = Missing::Value;

    private Identifier|People|Missing|null $reviewer = Missing::Value;

    /**
     * @var list<Identifier|People>|Missing
     */
    private array|Missing $watchers = Missing::Value;

    private Identifier|People|Missing|null $editor = Missing::Value;

    /**
     * Between 3 and 120 characters.
     *
     * @param non-empty-string $title
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Matches `^[a-z0-9]+(?:-[a-z0-9]+)*$`.
     */
    public function slug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function status(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function priority(Priority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * A multiple of 10.
     *
     * @param int<0, max>|null $wordCount
     */
    public function wordCount(?int $wordCount): self
    {
        $this->wordCount = $wordCount;

        return $this;
    }

    /**
     * Greater than 0 and less than 10.
     */
    public function rating(float $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function featured(bool $featured = true): self
    {
        $this->featured = $featured;

        return $this;
    }

    public function releasedOn(\DateTimeImmutable $releasedOn): self
    {
        $this->releasedOn = $releasedOn;

        return $this;
    }

    public function publishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function syncedAt(\DateTimeImmutable $syncedAt): self
    {
        $this->syncedAt = $syncedAt;

        return $this;
    }

    public function archivedAt(string $archivedAt): self
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function embargoLifts(string $embargoLifts): self
    {
        $this->embargoLifts = $embargoLifts;

        return $this;
    }

    /**
     * In `email` format.
     */
    public function editorEmail(string $editorEmail): self
    {
        $this->editorEmail = $editorEmail;

        return $this;
    }

    /**
     * In `uri` format.
     */
    public function canonicalUrl(string $canonicalUrl): self
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    /**
     * At most 280 characters.
     */
    public function excerpt(string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    /**
     * @param positive-int $revision
     */
    public function revision(int $revision): self
    {
        $this->revision = $revision;

        return $this;
    }

    public function reviewNote(?string $reviewNote): self
    {
        $this->reviewNote = $reviewNote;

        return $this;
    }

    public function originIp(string $originIp): self
    {
        $this->originIp = $originIp;

        return $this;
    }

    /**
     * At most 10 items; no duplicate items.
     *
     * @param non-empty-list<string> $keywords
     */
    public function keywords(array $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * @param array<string, mixed>|null $analytics
     */
    public function analytics(?array $analytics): self
    {
        $this->analytics = $analytics;

        return $this;
    }

    /**
     * @param array{street: non-empty-string, city?: string} $address
     */
    public function address(array $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param array{text: non-empty-string, kind: string}|array{src?: string, kind: string} $block
     */
    public function block(array $block): self
    {
        $this->block = $block;

        return $this;
    }

    /**
     * @param array{firstName?: string, nickname?: string, pin?: string, phone?: string, geo?: array{lat?: int}}|null $contact
     */
    public function contact(?array $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function authorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function score(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function author(Identifier|People|null $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @param list<Identifier|Tag> $tags
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function publisher(Identifier|People|null $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function cover(Identifier|Image|null $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * @param list<Identifier|People> $locked
     */
    public function locked(array $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @param list<Identifier|Image|Video> $attachments
     */
    public function attachments(array $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function reviewer(Identifier|People|null $reviewer): self
    {
        $this->reviewer = $reviewer;

        return $this;
    }

    /**
     * @param list<Identifier|People> $watchers
     */
    public function watchers(array $watchers): self
    {
        $this->watchers = $watchers;

        return $this;
    }

    public function editor(Identifier|People|null $editor): self
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): ArticleCreate
    {
        if ($this->title instanceof Missing) {
            throw MissingRequiredMemberException::for(ArticleBase::TYPE, ['title']);
        }

        return new ArticleCreate(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            revision: $this->revision,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            locked: $this->locked,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }
}

/**
 * The `editor` relationship of one article. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/articles/{id}/editor` and `/articles/{id}/relationships/editor`
 */
final class ArticleEditorRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone: the identifier of the related resource, or null, without fetching it.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "people",
     *             "id": "per-1"
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/articles/art-1/relationships/editor",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1editor/get"
     *             },
     *             "related": {
     *                 "href": "https://api.example.com/articles/art-1/editor",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1editor/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1editor/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/articles/' . $this->id . '/relationships/editor'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "editor" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): ?People
    {
        $context = $this->fetcher->read(Request::get('/articles/' . $this->id . '/editor'));
        $raw = $context->primary();

        return $raw === null ? null : People::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "people",
     *             "id": "per-1"
     *         }
     *     }
     */
    public function set(Identifier|People|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/articles/' . $this->id . '/relationships/editor',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * One article by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type ArticleUpdateShape from Article
 *
 * @generated from the `articles` operations on one resource
 */
final class ArticleHandle
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
     * Fetch the article, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(ArticleProjection<ArticleBase>): ArticleProjection<TProj>)|ArticleProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ArticleBase : ArticleBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "articles",
     *             "id": "art-1"
     *         }
     *     }
     */
    public function get(callable|ArticleProjection|null $projection = null): ArticleBase
    {
        $query = ArticleProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/articles/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The articles response', 'data');

        /** @var ArticleBase&TProj */
        return $context->hydrate($raw, Article::_hydrate(...));
    }

    /**
     * Patch the article, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param ArticleUpdate|ArticleUpdateShape                                                                   $input
     * @param (callable(ArticleProjection<ArticleBase>): ArticleProjection<TProj>)|ArticleProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ArticleBase : ArticleBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function update(
        ArticleUpdate|array $input,
        callable|ArticleProjection|null $projection = null,
    ): ArticleBase {
        $document = match (true) {
            $input instanceof ArticleUpdate => $input,
            default => ArticleUpdate::from($input),
        };

        $query = ArticleProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/articles/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var ArticleBase&TProj */
        return $context->hydrate($raw, Article::_hydrate(...));
    }

    /**
     * Delete the article.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "meta": {
     *             "deleted": true
     *         }
     *     }
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/articles/' . $this->id));
    }

    /**
     * The `attachments` relationship, a to-many whose endpoint advertises `add()`, `remove()` and
     * `replace()`.
     */
    public function attachments(): ArticleAttachmentsRelation
    {
        return new ArticleAttachmentsRelation($this->fetcher, $this->id);
    }

    /**
     * The `author` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function author(): ArticleAuthorRelation
    {
        return new ArticleAuthorRelation($this->fetcher, $this->id);
    }

    /**
     * The `cover` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function cover(): ArticleCoverRelation
    {
        return new ArticleCoverRelation($this->fetcher, $this->id);
    }

    /**
     * The `editor` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function editor(): ArticleEditorRelation
    {
        return new ArticleEditorRelation($this->fetcher, $this->id);
    }

    /**
     * The `locked` relationship, a to-many the document advertises no write for.
     */
    public function locked(): ArticleLockedRelation
    {
        return new ArticleLockedRelation($this->fetcher, $this->id);
    }

    /**
     * The `publisher` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function publisher(): ArticlePublisherRelation
    {
        return new ArticlePublisherRelation($this->fetcher, $this->id);
    }

    /**
     * The `tags` relationship, a to-many whose endpoint advertises `add()`, `remove()` and
     * `replace()`.
     */
    public function tags(): ArticleTagsRelation
    {
        return new ArticleTagsRelation($this->fetcher, $this->id);
    }

    /**
     * Resource-scoped custom actions, behind their own namespace because an action name may collide
     * with a relation or a verb.
     */
    public function actions(): ArticleActions
    {
        return new ArticleActions($this->fetcher, $this->id);
    }
}

/**
 * The `locked` relationship of one article. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. No write is advertised, so none is
 * generated.
 *
 * @generated from `/articles/{id}/locked` and `/articles/{id}/relationships/locked`
 */
final class ArticleLockedRelation
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
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": [
     *             {
     *                 "type": "people",
     *                 "id": "per-1"
     *             }
     *         ],
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/articles/art-1/relationships/locked",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1locked/get"
     *             },
     *             "related": {
     *                 "href": "https://api.example.com/articles/art-1/locked",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1locked/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1locked/get"
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
            $this->fetcher->read(Request::get('/articles/' . $this->id . '/relationships/locked')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<People, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/articles/' . $this->id . '/locked')));
    }

    /**
     * @return PaginatedCollection<Identifier, PageNumber>
     */
    private function linkage(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $identifiers = [];

        foreach ($context->primaryList() as $raw) {
            $identifiers[] = Identifier::fromArray($raw, 'the "locked" linkage');
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
     * @return PaginatedCollection<People, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $locked = [];

        foreach ($context->primaryList() as $raw) {
            $locked[] = People::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $locked,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }
}

/**
 * What a response carrying `articles` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /articles/{id}`, `POST /articles`, `PATCH /articles/{id}`, `POST /articles/{id}/-actions/publish`
 */
final class ArticleProjection extends Projection
{
    /** @use ArticleProjecting<TProj> */
    use ArticleProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<ArticleBase>
     */
    public static function make(): self
    {
        /** @var self<ArticleBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<ArticleBase>): self<T>)|self<T>|null $projection
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
 * The `publisher` relationship of one article. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `set()`, the
 * methods the relationship endpoint declares, and no others.
 *
 * @generated from `/articles/{id}/published-by` and `/articles/{id}/relationships/published-by`
 */
final class ArticlePublisherRelation
{
    public function __construct(
        private readonly Fetcher $fetcher,
        private readonly string $id,
    ) {}

    /**
     * The linkage alone: the identifier of the related resource, or null, without fetching it.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     *
     * @example 200 response:
     *     {
     *         "data": {
     *             "type": "people",
     *             "id": "per-1"
     *         },
     *         "links": {
     *             "self": {
     *                 "href": "https://api.example.com/articles/art-1/relationships/published-by",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1published-by/get"
     *             },
     *             "related": {
     *                 "href": "https://api.example.com/articles/art-1/published-by",
     *                 "describedby": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1published-by/get"
     *             },
     *             "describedby": {
     *                 "href": "/docs/openapi.json#/paths/~1articles~1%7Bid%7D~1relationships~1published-by/get"
     *             }
     *         },
     *         "jsonapi": {
     *             "version": "1.1"
     *         }
     *     }
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/articles/' . $this->id . '/relationships/published-by'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "publisher" linkage');
    }

    /**
     * The related resource itself, or null when the relationship is empty.
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): ?People
    {
        $context = $this->fetcher->read(Request::get('/articles/' . $this->id . '/published-by'));
        $raw = $context->primary();

        return $raw === null ? null : People::_hydrate($raw, $context);
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     *
     * @example Request body:
     *     {
     *         "data": {
     *             "type": "people",
     *             "id": "per-1"
     *         }
     *     }
     */
    public function set(Identifier|People|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/articles/' . $this->id . '/relationships/published-by',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * A read of `GET /articles`: the projection, plus everything only a read can carry.
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
 * @phpstan-import-type ArticleCountToken from Article
 * @phpstan-import-type ArticleDateRangeShape from Article
 * @phpstan-import-type ArticleFilterShape from Article
 * @phpstan-import-type ArticleIncludePath from Article
 * @phpstan-import-type ArticleRangeShape from Article
 * @phpstan-import-type ArticleSortToken from Article
 *
 * @generated from `GET /articles`
 */
final class ArticleQuery extends Query
{
    /** @use ArticleProjecting<TProj> */
    use ArticleProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Filter by `status`.
     *
     * @return static
     */
    public function whereStatus(string $value): static
    {
        return $this->filtering(['status' => $value]);
    }

    /**
     * Filter by `wordCount`.
     *
     * @return static
     */
    public function whereWordCount(int $value): static
    {
        return $this->filtering(['wordCount' => $value]);
    }

    /**
     * Filter by `archived`.
     *
     * @return static
     */
    public function whereArchived(string $value): static
    {
        return $this->filtering(['archived' => $value]);
    }

    /**
     * Filter by `rating`.
     *
     * @param ArticleRangeShape $range
     *
     * @return static
     */
    public function whereRating(array $range): static
    {
        return $this->filtering(['rating' => $range]);
    }

    /**
     * Filter by `publishedAt`.
     *
     * @param ArticleDateRangeShape $range
     *
     * @return static
     */
    public function wherePublishedAt(array $range): static
    {
        return $this->filtering(['publishedAt' => $range]);
    }

    /**
     * Filter by `labels`.
     *
     * @param list<string> $value
     *
     * @return static
     */
    public function whereLabels(array $value): static
    {
        return $this->filtering(['labels' => $value]);
    }

    /**
     * Filter by `search`.
     *
     * @return static
     */
    public function whereSearch(string $value): static
    {
        return $this->filtering(['search' => $value]);
    }

    /**
     * Filter by `urgent`.
     *
     * @return static
     */
    public function whereUrgent(string $value): static
    {
        return $this->filtering(['urgent' => $value]);
    }

    /**
     * Filter by `featured`.
     *
     * @return static
     */
    public function whereFeatured(bool $value): static
    {
        return $this->filtering(['featured' => $value]);
    }

    /**
     * Filter by `releasedOn`.
     *
     * @return static
     */
    public function whereReleasedOn(string $value): static
    {
        return $this->filtering(['releasedOn' => $value]);
    }

    /**
     * Filter by `keywords`.
     *
     * @param list<string> $value
     *
     * @return static
     */
    public function whereKeywords(array $value): static
    {
        return $this->filtering(['keywords' => $value]);
    }

    /**
     * Filter by `author.name`.
     *
     * @return static
     */
    public function whereAuthorName(string $value): static
    {
        return $this->filtering(['author.name' => $value]);
    }

    /**
     * Filter by `unpublished`.
     *
     * @return static
     */
    public function whereUnpublished(string $value): static
    {
        return $this->filtering(['unpublished' => $value]);
    }

    /**
     * The typed array door: values are checked statically, nested ones included, and names at
     * runtime.
     *
     * @param ArticleFilterShape $filter
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
     * @param ArticleSortToken ...$tokens
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
            if (!\in_array($token, Article::SORT_TOKENS, true)) {
                throw UnknownSortException::for($token, Article::SORT_TOKENS);
            }
        }

        return $this->sorting($tokens);
    }

    /**
     * Count related resources without including them, under the Countable profile. `GET /articles`
     * advertises `_self_`, `tags`. A counted relation is still not an included one: its count is
     * read through `_rel()`.
     *
     * @param list<ArticleCountToken> $tokens
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
     * Paginator parameters assembled at runtime. Public here because `GET /articles` is paginated;
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
     * The first article the read matches, fetching a single-resource page rather than a whole one.
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
     * @param ArticleFilterShape|array<string, mixed> $filter
     *
     * @return array<string, mixed>
     *
     * @throws UnknownFilterException
     */
    private static function guardFilters(array $filter): array
    {
        foreach ($filter as $name => $value) {
            $name = (string) $name;

            if (!\in_array($name, Article::FILTERS, true)) {
                throw UnknownFilterException::for($name, Article::FILTERS);
            }

            $members = Article::FILTER_MEMBERS[$name] ?? null;

            if ($members === null || !\is_array($value)) {
                continue;
            }

            $qualified = \array_map(static fn(string $member): string => $name . '[' . $member . ']', $members);

            foreach (\array_keys($value) as $member) {
                if (!\in_array((string) $member, $members, true)) {
                    throw UnknownFilterException::for($name . '[' . (string) $member . ']', $qualified);
                }
            }
        }

        return $filter;
    }

    /**
     * The Countable profile is negotiated only when a request actually uses a `withCount`
     * parameter, which is what `x-profile` on that parameter states.
     */
    private function request(ReadQuery $query): Request
    {
        $request = Request::get($query->appendTo('/articles'));

        return $query->withCount === [] ? $request : $request->withProfiles([Article::COUNTABLE_PROFILE]);
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $articles = [];

        foreach ($context->primaryList() as $raw) {
            $articles[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $articles,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Article`, but `TProj`
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
        return $context->hydrate($raw, Article::_hydrate(...));
    }
}

/**
 * A raw JSON response body, narrowed member by member: an optional member of the wrong type is
 * dropped rather than trusted, and a required one throws.
 *
 * @generated from `components.schemas.ArticleSummary`
 */
final class ArticleSummary
{
    /**
     * @param int<0, max> $total
     * @param array<string, mixed>|null $byStatus
     */
    public function __construct(
        public readonly int $total,
        public readonly ?array $byStatus = null,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $total = $raw['total'] ?? null;
        $byStatus = $raw['byStatus'] ?? null;

        $totalValue = null;
        if (!\is_int($total)) {
            throw MalformedDocument::unexpectedMember('The ArticleSummary body', 'total', 'an integer');
        }

        /** @var int<0, max> $total */
        $totalValue = $total;

        $byStatusValue = null;
        if (\is_array($byStatus)) {
            $byStatusValue = [];
            foreach ($byStatus as $byStatusKey => $byStatusItem) {
                $byStatusValue[(string) $byStatusKey] = $byStatusItem;
            }
        }

        return new self($totalValue, $byStatusValue);
    }
}

/**
 * One `tags` member and its pivot data, for a relationship write. Only the writable pivot fields
 * are here (`taggedAt` and `taggedBy` are read-only), and a required one takes no default, so
 * leaving it out fails before the request exists.
 *
 * @generated from the `meta.pivot` of `articles.tags` linkage
 */
final class ArticleTagsEdge
{
    public function __construct(
        public readonly Identifier|Tag $target,
        public readonly int $position,
        public readonly bool|Missing $primary = Missing::Value,
        public readonly string|Missing $reason = Missing::Value,
    ) {}

    /**
     * The linkage member this edge writes, its pivot under `meta.pivot`.
     *
     * @return array{type: string, id: string, meta: array{pivot: array<string, mixed>}}
     */
    public function toLinkage(): array
    {
        $pivot = [
            'position' => $this->position,
        ];

        if (!$this->primary instanceof Missing) {
            $pivot['primary'] = $this->primary;
        }

        if (!$this->reason instanceof Missing) {
            $pivot['reason'] = $this->reason;
        }

        $identifier = $this->target instanceof Identifier ? $this->target : $this->target->_identifier();

        return ['type' => $identifier->type, 'id' => $identifier->id, 'meta' => ['pivot' => $pivot]];
    }
}

/**
 * The `meta.pivot` of one `tags` linkage member of an `articles` resource.
 *
 * A field the server always renders reads as its type. Any other field reads as nullable, because a
 * pivot takes no sparse fieldset and a field the response leaves out is simply absent.
 *
 * @generated from the `meta.pivot` of `articles.tags` linkage
 */
final class ArticleTagsPivot extends Pivot
{
    public int $position { get => $this->position(); }
    public ?bool $primary { get => $this->primary(); }
    public \DateTimeImmutable $taggedAt { get => $this->taggedAt(); }
    public ?string $taggedBy { get => $this->taggedBy(); }

    public function position(): int
    {
        return $this->_int('position');
    }

    public function primary(): ?bool
    {
        return $this->_nullableBool('primary');
    }

    public function taggedAt(): \DateTimeImmutable
    {
        return $this->_dateTime('taggedAt');
    }

    public function taggedBy(): ?string
    {
        return $this->_nullableString('taggedBy');
    }
}

/**
 * The `tags` relationship of one article. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `add()`, `remove()`
 * and `replace()`, the methods the relationship endpoint declares, and no others. Members are
 * written as {@see ArticleTagsEdge}, which carries the writable pivot fields.
 *
 * @generated from `/articles/{id}/tags` and `/articles/{id}/relationships/tags`
 */
final class ArticleTagsRelation
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
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/articles/' . $this->id . '/relationships/tags')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Tag, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no article has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/articles/' . $this->id . '/tags')));
    }

    /**
     * Add members to the relationship, leaving the rest alone.
     *
     * @param list<ArticleTagsEdge> $tags
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function add(array $tags): void
    {
        $this->fetcher->send(Request::post(
            '/articles/' . $this->id . '/relationships/tags',
            \json_encode(['data' => self::edges($tags)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove members from the relationship, leaving the rest alone.
     *
     * @param list<Identifier|Tag> $tags
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function remove(array $tags): void
    {
        $this->fetcher->send(Request::delete(
            '/articles/' . $this->id . '/relationships/tags',
            \json_encode(['data' => Coerce::linkageList($tags)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Replace every member of the relationship.
     *
     * @param list<ArticleTagsEdge> $tags
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no article has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function replace(array $tags): void
    {
        $this->fetcher->send(Request::patch(
            '/articles/' . $this->id . '/relationships/tags',
            \json_encode(['data' => self::edges($tags)], \JSON_THROW_ON_ERROR),
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
            $identifiers[] = Identifier::fromArray($raw, 'the "tags" linkage');
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
     * @return PaginatedCollection<Tag, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $tags = [];

        foreach ($context->primaryList() as $raw) {
            $tags[] = Tag::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $tags,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }

    /**
     * @param list<ArticleTagsEdge> $edges
     *
     * @return list<array{type: string, id: string, meta: array{pivot: array<string, mixed>}}>
     */
    private static function edges(array $edges): array
    {
        return \array_map(static fn(ArticleTagsEdge $edge): array => $edge->toLinkage(), $edges);
    }
}

/**
 * The canonical update input for `articles`: every member optional, which is what a JSON:API
 * `PATCH` is. The id is not a member; it comes from the handle the update is called on, so it
 * cannot disagree with the URL.
 *
 * @phpstan-import-type ArticleUpdateShape from Article
 *
 * @generated from `PATCH /articles/{id}`
 */
final class ArticleUpdate
{
    use ImmutableConditionable;

    /**
     * @param non-empty-string|Missing $title
     * @param int<0, max>|Missing|null $wordCount
     * @param non-empty-list<string>|Missing $keywords
     * @param array<string, mixed>|Missing|null $analytics
     * @param array{street?: non-empty-string, city?: string}|Missing $address
     * @param array{text?: non-empty-string, kind?: string}|array{src?: string, kind?: string}|Missing $block
     * @param array{firstName?: string, nickname?: string, pin?: string, phone?: string, geo?: array{lat?: int}}|Missing|null $contact
     * @param Identifier|People|Missing|null $author
     * @param list<Identifier|Tag>|Missing $tags
     * @param Identifier|People|Missing|null $publisher
     * @param Identifier|Image|Missing|null $cover
     * @param list<Identifier|Image|Video>|Missing $attachments
     * @param Identifier|People|Missing|null $reviewer
     * @param list<Identifier|People>|Missing $watchers
     * @param Identifier|People|Missing|null $editor
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $title = Missing::Value,
        public readonly string|Missing $slug = Missing::Value,
        public readonly Status|Missing $status = Missing::Value,
        public readonly Priority|Missing $priority = Missing::Value,
        public readonly int|Missing|null $wordCount = Missing::Value,
        public readonly float|Missing $rating = Missing::Value,
        public readonly bool|Missing $featured = Missing::Value,
        public readonly \DateTimeImmutable|Missing $releasedOn = Missing::Value,
        public readonly \DateTimeImmutable|Missing|null $publishedAt = Missing::Value,
        public readonly \DateTimeImmutable|Missing $syncedAt = Missing::Value,
        public readonly string|Missing $archivedAt = Missing::Value,
        public readonly string|Missing $embargoLifts = Missing::Value,
        public readonly string|Missing $editorEmail = Missing::Value,
        public readonly string|Missing $canonicalUrl = Missing::Value,
        public readonly string|Missing $excerpt = Missing::Value,
        public readonly string|Missing|null $reviewNote = Missing::Value,
        public readonly string|Missing $originIp = Missing::Value,
        public readonly array|Missing $keywords = Missing::Value,
        public readonly array|Missing|null $analytics = Missing::Value,
        public readonly array|Missing $address = Missing::Value,
        public readonly array|Missing $block = Missing::Value,
        public readonly array|Missing|null $contact = Missing::Value,
        public readonly string|Missing $authorName = Missing::Value,
        public readonly int|Missing $score = Missing::Value,
        public readonly Identifier|People|Missing|null $author = Missing::Value,
        public readonly array|Missing $tags = Missing::Value,
        public readonly Identifier|People|Missing|null $publisher = Missing::Value,
        public readonly Identifier|Image|Missing|null $cover = Missing::Value,
        public readonly array|Missing $attachments = Missing::Value,
        public readonly Identifier|People|Missing|null $reviewer = Missing::Value,
        public readonly array|Missing $watchers = Missing::Value,
        public readonly Identifier|People|Missing|null $editor = Missing::Value,
    ) {}

    /**
     * Between 3 and 120 characters.
     *
     * @param non-empty-string $title
     *
     * @phpstan-pure
     */
    public function title(string $title): self
    {
        return new self(
            title: $title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * Matches `^[a-z0-9]+(?:-[a-z0-9]+)*$`.
     *
     * @phpstan-pure
     */
    public function slug(string $slug): self
    {
        return new self(
            title: $this->title,
            slug: $slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function status(Status $status): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function priority(Priority $priority): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * A multiple of 10.
     *
     * @param int<0, max>|null $wordCount
     *
     * @phpstan-pure
     */
    public function wordCount(?int $wordCount): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * Greater than 0 and less than 10.
     *
     * @phpstan-pure
     */
    public function rating(float $rating): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function featured(bool $featured = true): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function releasedOn(\DateTimeImmutable $releasedOn): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function publishedAt(?\DateTimeImmutable $publishedAt): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function syncedAt(\DateTimeImmutable $syncedAt): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function archivedAt(string $archivedAt): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function embargoLifts(string $embargoLifts): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * In `email` format.
     *
     * @phpstan-pure
     */
    public function editorEmail(string $editorEmail): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * In `uri` format.
     *
     * @phpstan-pure
     */
    public function canonicalUrl(string $canonicalUrl): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function excerpt(string $excerpt): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function reviewNote(?string $reviewNote): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function originIp(string $originIp): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * At most 10 items; no duplicate items.
     *
     * @param non-empty-list<string> $keywords
     *
     * @phpstan-pure
     */
    public function keywords(array $keywords): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @param array<string, mixed>|null $analytics
     *
     * @phpstan-pure
     */
    public function analytics(?array $analytics): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @param array{street?: non-empty-string, city?: string} $address
     *
     * @phpstan-pure
     */
    public function address(array $address): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @param array{text?: non-empty-string, kind?: string}|array{src?: string, kind?: string} $block
     *
     * @phpstan-pure
     */
    public function block(array $block): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @param array{firstName?: string, nickname?: string, pin?: string, phone?: string, geo?: array{lat?: int}}|null $contact
     *
     * @phpstan-pure
     */
    public function contact(?array $contact): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function authorName(string $authorName): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function score(int $score): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function author(Identifier|People|null $author): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @param list<Identifier|Tag> $tags
     *
     * @phpstan-pure
     */
    public function tags(array $tags): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function publisher(Identifier|People|null $publisher): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function cover(Identifier|Image|null $cover): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @param list<Identifier|Image|Video> $attachments
     *
     * @phpstan-pure
     */
    public function attachments(array $attachments): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function reviewer(Identifier|People|null $reviewer): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $reviewer,
            watchers: $this->watchers,
            editor: $this->editor,
        );
    }

    /**
     * @param list<Identifier|People> $watchers
     *
     * @phpstan-pure
     */
    public function watchers(array $watchers): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $watchers,
            editor: $this->editor,
        );
    }

    /**
     * @phpstan-pure
     */
    public function editor(Identifier|People|null $editor): self
    {
        return new self(
            title: $this->title,
            slug: $this->slug,
            status: $this->status,
            priority: $this->priority,
            wordCount: $this->wordCount,
            rating: $this->rating,
            featured: $this->featured,
            releasedOn: $this->releasedOn,
            publishedAt: $this->publishedAt,
            syncedAt: $this->syncedAt,
            archivedAt: $this->archivedAt,
            embargoLifts: $this->embargoLifts,
            editorEmail: $this->editorEmail,
            canonicalUrl: $this->canonicalUrl,
            excerpt: $this->excerpt,
            reviewNote: $this->reviewNote,
            originIp: $this->originIp,
            keywords: $this->keywords,
            analytics: $this->analytics,
            address: $this->address,
            block: $this->block,
            contact: $this->contact,
            authorName: $this->authorName,
            score: $this->score,
            author: $this->author,
            tags: $this->tags,
            publisher: $this->publisher,
            cover: $this->cover,
            attachments: $this->attachments,
            reviewer: $this->reviewer,
            watchers: $this->watchers,
            editor: $editor,
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
     * @param ArticleUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `articles`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Article::WRITABLE_ON_UPDATE, true)) {
                throw UnknownAttributeException::for(ArticleBase::TYPE, (string) $member, Article::WRITABLE_ON_UPDATE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            title: \array_key_exists('title', $input) ? $input['title'] : Missing::Value,
            slug: \array_key_exists('slug', $input) ? $input['slug'] : Missing::Value,
            status: Coerce::enum(Status::class, \array_key_exists('status', $input) ? $input['status'] : Missing::Value),
            priority: Coerce::enum(Priority::class, \array_key_exists('priority', $input) ? $input['priority'] : Missing::Value),
            wordCount: \array_key_exists('wordCount', $input) ? $input['wordCount'] : Missing::Value,
            rating: \array_key_exists('rating', $input) ? $input['rating'] : Missing::Value,
            featured: \array_key_exists('featured', $input) ? $input['featured'] : Missing::Value,
            releasedOn: Coerce::dateTime(\array_key_exists('releasedOn', $input) ? $input['releasedOn'] : Missing::Value),
            publishedAt: Coerce::nullableDateTime(\array_key_exists('publishedAt', $input) ? $input['publishedAt'] : Missing::Value),
            syncedAt: Coerce::dateTime(\array_key_exists('syncedAt', $input) ? $input['syncedAt'] : Missing::Value),
            archivedAt: \array_key_exists('archivedAt', $input) ? $input['archivedAt'] : Missing::Value,
            embargoLifts: \array_key_exists('embargoLifts', $input) ? $input['embargoLifts'] : Missing::Value,
            editorEmail: \array_key_exists('editorEmail', $input) ? $input['editorEmail'] : Missing::Value,
            canonicalUrl: \array_key_exists('canonicalUrl', $input) ? $input['canonicalUrl'] : Missing::Value,
            excerpt: \array_key_exists('excerpt', $input) ? $input['excerpt'] : Missing::Value,
            reviewNote: \array_key_exists('reviewNote', $input) ? $input['reviewNote'] : Missing::Value,
            originIp: \array_key_exists('originIp', $input) ? $input['originIp'] : Missing::Value,
            keywords: \array_key_exists('keywords', $input) ? $input['keywords'] : Missing::Value,
            analytics: \array_key_exists('analytics', $input) ? $input['analytics'] : Missing::Value,
            address: \array_key_exists('address', $input) ? $input['address'] : Missing::Value,
            block: \array_key_exists('block', $input) ? $input['block'] : Missing::Value,
            contact: \array_key_exists('contact', $input) ? $input['contact'] : Missing::Value,
            authorName: \array_key_exists('authorName', $input) ? $input['authorName'] : Missing::Value,
            score: \array_key_exists('score', $input) ? $input['score'] : Missing::Value,
            author: \array_key_exists('author', $input) ? $input['author'] : Missing::Value,
            tags: \array_key_exists('tags', $input) ? $input['tags'] : Missing::Value,
            publisher: \array_key_exists('publisher', $input) ? $input['publisher'] : Missing::Value,
            cover: \array_key_exists('cover', $input) ? $input['cover'] : Missing::Value,
            attachments: \array_key_exists('attachments', $input) ? $input['attachments'] : Missing::Value,
            reviewer: \array_key_exists('reviewer', $input) ? $input['reviewer'] : Missing::Value,
            watchers: \array_key_exists('watchers', $input) ? $input['watchers'] : Missing::Value,
            editor: \array_key_exists('editor', $input) ? $input['editor'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /articles/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => ArticleBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->title instanceof Missing) {
            $attributes['title'] = $this->title;
        }

        if (!$this->slug instanceof Missing) {
            $attributes['slug'] = $this->slug;
        }

        if (!$this->status instanceof Missing) {
            $attributes['status'] = $this->status->value;
        }

        if (!$this->priority instanceof Missing) {
            $attributes['priority'] = $this->priority->value;
        }

        if (!$this->wordCount instanceof Missing) {
            $attributes['wordCount'] = $this->wordCount;
        }

        if (!$this->rating instanceof Missing) {
            $attributes['rating'] = $this->rating;
        }

        if (!$this->featured instanceof Missing) {
            $attributes['featured'] = $this->featured;
        }

        if (!$this->releasedOn instanceof Missing) {
            $attributes['releasedOn'] = $this->releasedOn->format('Y-m-d');
        }

        if (!$this->publishedAt instanceof Missing) {
            $attributes['publishedAt'] = $this->publishedAt?->format(\DateTimeInterface::ATOM);
        }

        if (!$this->syncedAt instanceof Missing) {
            $attributes['syncedAt'] = $this->syncedAt->format(\DateTimeInterface::ATOM);
        }

        if (!$this->archivedAt instanceof Missing) {
            $attributes['archivedAt'] = $this->archivedAt;
        }

        if (!$this->embargoLifts instanceof Missing) {
            $attributes['embargoLifts'] = $this->embargoLifts;
        }

        if (!$this->editorEmail instanceof Missing) {
            $attributes['editorEmail'] = $this->editorEmail;
        }

        if (!$this->canonicalUrl instanceof Missing) {
            $attributes['canonicalUrl'] = $this->canonicalUrl;
        }

        if (!$this->excerpt instanceof Missing) {
            $attributes['excerpt'] = $this->excerpt;
        }

        if (!$this->reviewNote instanceof Missing) {
            $attributes['reviewNote'] = $this->reviewNote;
        }

        if (!$this->originIp instanceof Missing) {
            $attributes['originIp'] = $this->originIp;
        }

        if (!$this->keywords instanceof Missing) {
            $attributes['keywords'] = $this->keywords;
        }

        if (!$this->analytics instanceof Missing) {
            $attributes['analytics'] = $this->analytics;
        }

        if (!$this->address instanceof Missing) {
            $attributes['address'] = $this->address;
        }

        if (!$this->block instanceof Missing) {
            $attributes['block'] = $this->block;
        }

        if (!$this->contact instanceof Missing) {
            $attributes['contact'] = $this->contact;
        }

        if (!$this->authorName instanceof Missing) {
            $attributes['authorName'] = $this->authorName;
        }

        if (!$this->score instanceof Missing) {
            $attributes['score'] = $this->score;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->author instanceof Missing) {
            $relationships['author'] = ['data' => Coerce::linkage($this->author)];
        }

        if (!$this->tags instanceof Missing) {
            $relationships['tags'] = ['data' => Coerce::linkageList($this->tags)];
        }

        if (!$this->publisher instanceof Missing) {
            $relationships['publisher'] = ['data' => Coerce::linkage($this->publisher)];
        }

        if (!$this->cover instanceof Missing) {
            $relationships['cover'] = ['data' => Coerce::linkage($this->cover)];
        }

        if (!$this->attachments instanceof Missing) {
            $relationships['attachments'] = ['data' => Coerce::linkageList($this->attachments)];
        }

        if (!$this->reviewer instanceof Missing) {
            $relationships['reviewer'] = ['data' => Coerce::linkage($this->reviewer)];
        }

        if (!$this->watchers instanceof Missing) {
            $relationships['watchers'] = ['data' => Coerce::linkageList($this->watchers)];
        }

        if (!$this->editor instanceof Missing) {
            $relationships['editor'] = ['data' => Coerce::linkage($this->editor)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The `articles` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type ArticleCreateShape from Article
 *
 * @generated from the `articles` operations
 */
final class Articles
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the articles collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return ArticleQuery<ArticleBase>
     */
    public function query(): ArticleQuery
    {
        /** @var ArticleQuery<ArticleBase> */
        return new ArticleQuery($this->fetcher);
    }

    /**
     * A handle on one article by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): ArticleHandle
    {
        return new ArticleHandle($this->fetcher, $id);
    }

    /**
     * Create an article, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param ArticleCreate|ArticleCreateBuilder|ArticleCreateShape                                              $input
     * @param (callable(ArticleProjection<ArticleBase>): ArticleProjection<TProj>)|ArticleProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ArticleBase : ArticleBase&TProj)
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
        ArticleCreate|ArticleCreateBuilder|array $input,
        callable|ArticleProjection|null $projection = null,
    ): ArticleBase {
        $document = match (true) {
            $input instanceof ArticleCreate => $input,
            $input instanceof ArticleCreateBuilder => $input->build(),
            default => ArticleCreate::from($input),
        };

        $query = ArticleProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/articles'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var ArticleBase&TProj */
        return $context->hydrate($raw, Article::_hydrate(...));
    }

    /**
     * Collection-scoped custom actions, behind their own namespace because an action name may
     * collide with a relation or a verb.
     */
    public function actions(): ArticleCollectionActions
    {
        return new ArticleCollectionActions($this->fetcher);
    }
}
