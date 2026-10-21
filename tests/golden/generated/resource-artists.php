<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\ResourceCollection;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Coerce;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\ImmutableConditionable;
use haddowg\JsonApiClient\Support\Missing;

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

    public string $name { get => $this->name(); }

    public string $slug { get => $this->slug(); }

    public ?string $website { get => $this->website(); }

    public ?string $bio { get => $this->bio(); }

    public int $trackCount { get => $this->trackCount(); }

    public \DateTimeImmutable $createdAt { get => $this->createdAt(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        return $this->_string('name');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `slug`
     */
    public function slug(): string
    {
        return $this->_string('slug');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `website`
     */
    public function website(): ?string
    {
        return $this->_nullableString('website');
    }

    /**
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
    public function createdAt(): \DateTimeImmutable
    {
        return $this->_dateTime('createdAt');
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
 * @phpstan-type ArtistCreateShape array{name: string, slug?: string, website?: string|null, bio?: string|null, createdAt?: ArtistDateInput, albums?: ArtistAlbumsInput}
 * @phpstan-type ArtistUpdateShape array{name?: string, slug?: string, website?: string|null, bio?: string|null, albums?: ArtistAlbumsInput}
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

    private string|Missing $name = Missing::Value;

    private string|Missing $slug = Missing::Value;

    private string|Missing|null $website = Missing::Value;

    private string|Missing|null $bio = Missing::Value;

    private \DateTimeImmutable|Missing $createdAt = Missing::Value;

    /**
     * @var list<Album|Identifier>|Missing
     */
    private array|Missing $albums = Missing::Value;

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

    public function website(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

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
