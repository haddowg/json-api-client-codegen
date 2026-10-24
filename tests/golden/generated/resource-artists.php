<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

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
use haddowg\JsonApiClient\Http\Request;
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
 * The `albums` relation of an `artists` resource, hydrated.
 *
 * A marker carries no implementation. `withAlbums()` intersects it into a query's projection, which
 * is what makes `albums()` type-check after that include and fail without it.
 *
 * @generated from `components.schemas.ArtistsResource.properties.relationships.albums`
 */
interface ArtistHasAlbums
{
    /**
     * @return ResourceCollection<Album>
     *
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `albums`
     */
    public function albums(): ResourceCollection;

    /** @var ResourceCollection<Album> */
    public ResourceCollection $albums { get; }
}

/**
 * The projection surface of `artists`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see ArtistProjection} and {@see ArtistQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `artists` read operations
 */
trait ArtistProjecting
{
    /**
     * Include the `albums` relation, narrowing the projection so `$artist->albums` type-checks.
     *
     * @return static<TProj&ArtistHasAlbums>
     */
    public function withAlbums(): static
    {
        return $this->including('albums');
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
     * Sparse fieldsets, with the `artists` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[artists]` names a member `artists` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[ArtistBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Artist::FIELDS, true)) {
                throw UnknownFieldException::for(ArtistBase::TYPE, $field, Artist::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * An `artists` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.ArtistsResource`
 */
abstract class ArtistBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'artists';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    /** @var non-empty-string */
    public string $name { get => $this->name(); }

    public string $slug { get => $this->slug(); }

    public ?string $website { get => $this->website(); }

    public ?string $bio { get => $this->bio(); }

    public int $trackCount { get => $this->trackCount(); }

    public ?\DateTimeImmutable $createdAt { get => $this->createdAt(); }

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
     * At most 120 characters.
     *
     * @return non-empty-string
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('name');

        return $value;
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `slug`
     */
    public function slug(): string
    {
        return $this->_string('slug');
    }

    /**
     * In `uri` format.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `website`
     */
    public function website(): ?string
    {
        return $this->_nullableString('website');
    }

    /**
     * At most 1000 characters.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `bio`
     */
    public function bio(): ?string
    {
        return $this->_nullableString('bio');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `trackCount`
     */
    public function trackCount(): int
    {
        return $this->_int('trackCount');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `createdAt`
     */
    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->_nullableDateTime('createdAt');
    }

    /**
     * The `albums` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function albumsRef(): array
    {
        return $this->_relationship('albums', false)->identifiers();
    }

    /**
     * Whether the `albums` linkage points at anything. Never throws.
     */
    public function hasAlbums(): bool
    {
        return !$this->_relationship('albums', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'albums' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, false);
    }
}

/**
 * An `artists` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `artists` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type ArtistDateInput \DateTimeImmutable|string
 * @phpstan-type ArtistAlbumsInput list<Album|Identifier>
 * @phpstan-type ArtistCreateShape array{name: non-empty-string, slug?: string, website?: string|null, bio?: string|null, createdAt?: ArtistDateInput, albums?: ArtistAlbumsInput}
 * @phpstan-type ArtistUpdateShape array{name?: non-empty-string, slug?: string, website?: string|null, bio?: string|null, albums?: ArtistAlbumsInput}
 * @phpstan-type ArtistFilterShape array{slug?: string, q?: string}
 * @phpstan-type ArtistSortToken '-createdAt'|'-name'|'-slug'|'createdAt'|'name'|'slug'
 * @phpstan-type ArtistIncludePath 'albums'|'albums.artist'|'albums.tracks'
 * @phpstan-type ArtistFieldToken 'albums'|'bio'|'createdAt'|'name'|'slug'|'trackCount'|'website'
 *
 * @generated from `components.schemas.ArtistsResource`
 */
final class Artist extends ArtistBase implements ArtistHasAlbums
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['name', 'slug', 'website', 'bio', 'createdAt', 'albums'];

    /**
     * The members the update document accepts, where they differ from `WRITABLE`.
     *
     * @var list<string>
     */
    public const array WRITABLE_ON_UPDATE = ['name', 'slug', 'website', 'bio', 'albums'];

    /**
     * Members a create document requires. Read-only attributes (`trackCount`) are in no write
     * shape.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['name'];

    /**
     * The filters `GET /artists` accepts. The runtime guard on both array doors checks names
     * against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array FILTERS = ['slug', 'q'];

    /**
     * The signed sort tokens `GET /artists` accepts, both directions of each.
     *
     * @var list<string>
     */
    public const array SORT_TOKENS = ['name', '-name', 'slug', '-slug', 'createdAt', '-createdAt'];

    /**
     * The members `fields[artists]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = [
        'name',
        'slug',
        'website',
        'bio',
        'trackCount',
        'createdAt',
        'albums',
    ];

    /** @var ResourceCollection<Album> */
    public ResourceCollection $albums { get => $this->albums(); }

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not an `artists` one
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
     * A resource identifier for an `artists` resource that is not in hand, for a relationship
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
     * @return ArtistProjection<ArtistBase>
     */
    public static function projection(): ArtistProjection
    {
        return ArtistProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param ArtistCreateShape|null $input
     *
     * @return ($input is null ? ArtistCreateBuilder : ArtistCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `artists` does not have
     */
    public static function create(?array $input = null): ArtistCreateBuilder|ArtistCreate
    {
        return $input === null ? new ArtistCreateBuilder() : ArtistCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param ArtistUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `artists` does not have
     */
    public static function update(?array $input = null): ArtistUpdate
    {
        return $input === null ? new ArtistUpdate() : ArtistUpdate::from($input);
    }

    /**
     * @return ResourceCollection<Album>
     *
     * @throws RelationNotIncludedException when the read did not include `albums`
     */
    public function albums(): ResourceCollection
    {
        $context = $this->_child('albums');
        $albums = [];

        foreach ($this->_includedMany('albums', 'albumsRef') as $raw) {
            $albums[] = Album::_hydrate($raw, $context);
        }

        return new ResourceCollection($albums);
    }
}

/**
 * The `albums` relationship of one artist. `get()` reads the linkage and `related()` reads the
 * related resources; any read the server switched off is absent. The writes are `add()`, `remove()`
 * and `replace()`, the methods the relationship endpoint declares, and no others.
 *
 * @generated from `/artists/{id}/albums` and `/artists/{id}/relationships/albums`
 */
final class ArtistAlbumsRelation
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
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no artist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): PaginatedCollection
    {
        return $this->linkage(
            $this->fetcher->read(Request::get('/artists/' . $this->id . '/relationships/albums')),
        );
    }

    /**
     * The related resources themselves, a page at a time.
     *
     * @return PaginatedCollection<Album, PageNumber>
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no artist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function related(): PaginatedCollection
    {
        return $this->resources($this->fetcher->read(Request::get('/artists/' . $this->id . '/albums')));
    }

    /**
     * Add members to the relationship, leaving the rest alone.
     *
     * @param list<Album|Identifier> $albums
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no artist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function add(array $albums): void
    {
        $this->fetcher->send(Request::post(
            '/artists/' . $this->id . '/relationships/albums',
            \json_encode(['data' => Coerce::linkageList($albums)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Remove members from the relationship, leaving the rest alone.
     *
     * @param list<Album|Identifier> $albums
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no artist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function remove(array $albums): void
    {
        $this->fetcher->send(Request::delete(
            '/artists/' . $this->id . '/relationships/albums',
            \json_encode(['data' => Coerce::linkageList($albums)], \JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * Replace every member of the relationship.
     *
     * @param list<Album|Identifier> $albums
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no artist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function replace(array $albums): void
    {
        $this->fetcher->send(Request::patch(
            '/artists/' . $this->id . '/relationships/albums',
            \json_encode(['data' => Coerce::linkageList($albums)], \JSON_THROW_ON_ERROR),
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
            $identifiers[] = Identifier::fromArray($raw, 'the "albums" linkage');
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
     * @return PaginatedCollection<Album, PageNumber>
     */
    private function resources(ResourceContext $context): PaginatedCollection
    {
        $document = $context->document();
        $albums = [];

        foreach ($context->primaryList() as $raw) {
            $albums[] = Album::_hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $albums,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),
        );
    }
}

/**
 * The canonical create input for `artists`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type ArtistCreateShape from Artist
 *
 * @generated from `POST /artists`
 */
final class ArtistCreate
{
    /**
     * @param non-empty-string $name
     * @param list<Album|Identifier>|Missing $albums
     */
    public function __construct(
        public readonly string $name,
        public readonly string|Missing $slug = Missing::Value,
        public readonly string|Missing|null $website = Missing::Value,
        public readonly string|Missing|null $bio = Missing::Value,
        public readonly \DateTimeImmutable|Missing $createdAt = Missing::Value,
        public readonly array|Missing $albums = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param ArtistCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `artists`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Artist::WRITABLE, true)) {
                throw UnknownAttributeException::for(ArtistBase::TYPE, (string) $member, Artist::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: $input['name'],
            slug: \array_key_exists('slug', $input) ? $input['slug'] : Missing::Value,
            website: \array_key_exists('website', $input) ? $input['website'] : Missing::Value,
            bio: \array_key_exists('bio', $input) ? $input['bio'] : Missing::Value,
            createdAt: Coerce::dateTime(\array_key_exists('createdAt', $input) ? $input['createdAt'] : Missing::Value),
            albums: \array_key_exists('albums', $input) ? $input['albums'] : Missing::Value,
        );
    }

    /**
     * The `POST /artists` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => ArtistBase::TYPE];

        $attributes = ['name' => $this->name];

        if (!$this->slug instanceof Missing) {
            $attributes['slug'] = $this->slug;
        }

        if (!$this->website instanceof Missing) {
            $attributes['website'] = $this->website;
        }

        if (!$this->bio instanceof Missing) {
            $attributes['bio'] = $this->bio;
        }

        if (!$this->createdAt instanceof Missing) {
            $attributes['createdAt'] = $this->createdAt->format(\DateTimeInterface::ATOM);
        }

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->albums instanceof Missing) {
            $relationships['albums'] = ['data' => Coerce::linkageList($this->albums)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `artists`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /artists`
 */
final class ArtistCreateBuilder
{
    use Conditionable;

    /**
     * @var non-empty-string|Missing
     */
    private string|Missing $name = Missing::Value;

    private string|Missing $slug = Missing::Value;

    private string|Missing|null $website = Missing::Value;

    private string|Missing|null $bio = Missing::Value;

    private \DateTimeImmutable|Missing $createdAt = Missing::Value;

    /**
     * @var list<Album|Identifier>|Missing
     */
    private array|Missing $albums = Missing::Value;

    /**
     * At most 120 characters.
     *
     * @param non-empty-string $name
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function slug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * In `uri` format.
     */
    public function website(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    /**
     * At most 1000 characters.
     */
    public function bio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function createdAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param list<Album|Identifier> $albums
     */
    public function albums(array $albums): self
    {
        $this->albums = $albums;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): ArtistCreate
    {
        if ($this->name instanceof Missing) {
            throw MissingRequiredMemberException::for(ArtistBase::TYPE, ['name']);
        }

        return new ArtistCreate(
            name: $this->name,
            slug: $this->slug,
            website: $this->website,
            bio: $this->bio,
            createdAt: $this->createdAt,
            albums: $this->albums,
        );
    }
}

/**
 * One artist by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type ArtistUpdateShape from Artist
 *
 * @generated from the `artists` operations on one resource
 */
final class ArtistHandle
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
     * Fetch the artist, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(ArtistProjection<ArtistBase>): ArtistProjection<TProj>)|ArtistProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ArtistBase : ArtistBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no artist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|ArtistProjection|null $projection = null): ArtistBase
    {
        $query = ArtistProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/artists/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The artists response', 'data');

        /** @var ArtistBase&TProj */
        return $context->hydrate($raw, Artist::_hydrate(...));
    }

    /**
     * Patch the artist, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param ArtistUpdate|ArtistUpdateShape                                                                 $input
     * @param (callable(ArtistProjection<ArtistBase>): ArtistProjection<TProj>)|ArtistProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ArtistBase : ArtistBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no artist has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function update(
        ArtistUpdate|array $input,
        callable|ArtistProjection|null $projection = null,
    ): ArtistBase {
        $document = match (true) {
            $input instanceof ArtistUpdate => $input,
            default => ArtistUpdate::from($input),
        };

        $query = ArtistProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/artists/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var ArtistBase&TProj */
        return $context->hydrate($raw, Artist::_hydrate(...));
    }

    /**
     * Delete the artist.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no artist has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/artists/' . $this->id));
    }

    /**
     * The `albums` relationship, a to-many whose endpoint advertises `add()`, `remove()` and
     * `replace()`.
     */
    public function albums(): ArtistAlbumsRelation
    {
        return new ArtistAlbumsRelation($this->fetcher, $this->id);
    }
}

/**
 * What a response carrying `artists` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /artists/{id}`, `POST /artists`, `PATCH /artists/{id}`
 */
final class ArtistProjection extends Projection
{
    /** @use ArtistProjecting<TProj> */
    use ArtistProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<ArtistBase>
     */
    public static function make(): self
    {
        /** @var self<ArtistBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<ArtistBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /artists`: the projection, plus everything only a read can carry.
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
 * @phpstan-import-type ArtistFilterShape from Artist
 * @phpstan-import-type ArtistIncludePath from Artist
 * @phpstan-import-type ArtistSortToken from Artist
 *
 * @generated from `GET /artists`
 */
final class ArtistQuery extends Query
{
    /** @use ArtistProjecting<TProj> */
    use ArtistProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * Filter by `slug`.
     *
     * @return static
     */
    public function whereSlug(string $value): static
    {
        return $this->filtering(['slug' => $value]);
    }

    /**
     * Filter by `q`.
     *
     * @return static
     */
    public function whereQ(string $value): static
    {
        return $this->filtering(['q' => $value]);
    }

    /**
     * The typed array door: values are checked statically, nested ones included, and names at
     * runtime.
     *
     * @param ArtistFilterShape $filter
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
     * @param ArtistSortToken ...$tokens
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
            if (!\in_array($token, Artist::SORT_TOKENS, true)) {
                throw UnknownSortException::for($token, Artist::SORT_TOKENS);
            }
        }

        return $this->sorting($tokens);
    }

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
     * Paginator parameters assembled at runtime. Public here because `GET /artists` is paginated;
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
     * The first artist the read matches, fetching a single-resource page rather than a whole one.
     *
     * @return TProj|null
     *
     * @throws BadRequest         when a query parameter is rejected
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
     * @param ArtistFilterShape|array<string, mixed> $filter
     *
     * @return array<string, mixed>
     *
     * @throws UnknownFilterException
     */
    private static function guardFilters(array $filter): array
    {
        foreach (\array_keys($filter) as $name) {
            $name = (string) $name;

            if (!\in_array($name, Artist::FILTERS, true)) {
                throw UnknownFilterException::for($name, Artist::FILTERS);
            }
        }

        return $filter;
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/artists'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $artists = [];

        foreach ($context->primaryList() as $raw) {
            $artists[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $artists,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Artist`, but `TProj`
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
        return $context->hydrate($raw, Artist::_hydrate(...));
    }
}

/**
 * The canonical update input for `artists`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type ArtistUpdateShape from Artist
 *
 * @generated from `PATCH /artists/{id}`
 */
final class ArtistUpdate
{
    use ImmutableConditionable;

    /**
     * @param non-empty-string|Missing $name
     * @param list<Album|Identifier>|Missing $albums
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $name = Missing::Value,
        public readonly string|Missing $slug = Missing::Value,
        public readonly string|Missing|null $website = Missing::Value,
        public readonly string|Missing|null $bio = Missing::Value,
        public readonly array|Missing $albums = Missing::Value,
    ) {}

    /**
     * At most 120 characters.
     *
     * @param non-empty-string $name
     *
     * @phpstan-pure
     */
    public function name(string $name): self
    {
        return new self(
            name: $name,
            slug: $this->slug,
            website: $this->website,
            bio: $this->bio,
            albums: $this->albums,
        );
    }

    /**
     * @phpstan-pure
     */
    public function slug(string $slug): self
    {
        return new self(
            name: $this->name,
            slug: $slug,
            website: $this->website,
            bio: $this->bio,
            albums: $this->albums,
        );
    }

    /**
     * In `uri` format.
     *
     * @phpstan-pure
     */
    public function website(?string $website): self
    {
        return new self(
            name: $this->name,
            slug: $this->slug,
            website: $website,
            bio: $this->bio,
            albums: $this->albums,
        );
    }

    /**
     * At most 1000 characters.
     *
     * @phpstan-pure
     */
    public function bio(?string $bio): self
    {
        return new self(
            name: $this->name,
            slug: $this->slug,
            website: $this->website,
            bio: $bio,
            albums: $this->albums,
        );
    }

    /**
     * @param list<Album|Identifier> $albums
     *
     * @phpstan-pure
     */
    public function albums(array $albums): self
    {
        return new self(
            name: $this->name,
            slug: $this->slug,
            website: $this->website,
            bio: $this->bio,
            albums: $albums,
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
     * @param ArtistUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `artists`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Artist::WRITABLE_ON_UPDATE, true)) {
                throw UnknownAttributeException::for(ArtistBase::TYPE, (string) $member, Artist::WRITABLE_ON_UPDATE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: \array_key_exists('name', $input) ? $input['name'] : Missing::Value,
            slug: \array_key_exists('slug', $input) ? $input['slug'] : Missing::Value,
            website: \array_key_exists('website', $input) ? $input['website'] : Missing::Value,
            bio: \array_key_exists('bio', $input) ? $input['bio'] : Missing::Value,
            albums: \array_key_exists('albums', $input) ? $input['albums'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /artists/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => ArtistBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->name instanceof Missing) {
            $attributes['name'] = $this->name;
        }

        if (!$this->slug instanceof Missing) {
            $attributes['slug'] = $this->slug;
        }

        if (!$this->website instanceof Missing) {
            $attributes['website'] = $this->website;
        }

        if (!$this->bio instanceof Missing) {
            $attributes['bio'] = $this->bio;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->albums instanceof Missing) {
            $relationships['albums'] = ['data' => Coerce::linkageList($this->albums)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The `artists` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type ArtistCreateShape from Artist
 *
 * @generated from the `artists` operations
 */
final class Artists
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the artists collection, not yet sent: unprojected, unfiltered and unsorted.
     *
     * @return ArtistQuery<ArtistBase>
     */
    public function query(): ArtistQuery
    {
        /** @var ArtistQuery<ArtistBase> */
        return new ArtistQuery($this->fetcher);
    }

    /**
     * A handle on one artist by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): ArtistHandle
    {
        return new ArtistHandle($this->fetcher, $id);
    }

    /**
     * Create an artist, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * @template TProj of object
     *
     * @param ArtistCreate|ArtistCreateBuilder|ArtistCreateShape                                             $input
     * @param (callable(ArtistProjection<ArtistBase>): ArtistProjection<TProj>)|ArtistProjection<TProj>|null $projection
     *
     * @return ($projection is null ? ArtistBase : ArtistBase&TProj)
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
        ArtistCreate|ArtistCreateBuilder|array $input,
        callable|ArtistProjection|null $projection = null,
    ): ArtistBase {
        $document = match (true) {
            $input instanceof ArtistCreate => $input,
            $input instanceof ArtistCreateBuilder => $input->build(),
            default => ArtistCreate::from($input),
        };

        $query = ArtistProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/artists'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var ArtistBase&TProj */
        return $context->hydrate($raw, Artist::_hydrate(...));
    }
}
