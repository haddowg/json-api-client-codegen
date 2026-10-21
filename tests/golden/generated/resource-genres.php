<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\ImmutableConditionable;
use haddowg\JsonApiClient\Support\Missing;

/**
 * A `genres` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.GenresResource`
 */
abstract class GenreBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'genres';

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        return $this->_string('name');
    }
}

/**
 * A `genres` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `genres` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type GenreCreateShape array{id: string, name: string}
 * @phpstan-type GenreUpdateShape array{name?: string}
 *
 * @generated from `components.schemas.GenresResource`
 */
final class Genre extends GenreBase
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['name'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['name'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `genres` one
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
     * A resource identifier for a `genres` resource that is not in hand, for a relationship write.
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
     * @return GenreProjection<GenreBase>
     */
    public static function projection(): GenreProjection
    {
        return GenreProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param GenreCreateShape|null $input
     *
     * @return ($input is null ? GenreCreateBuilder : GenreCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `genres` does not have
     */
    public static function create(?array $input = null): GenreCreateBuilder|GenreCreate
    {
        return $input === null ? new GenreCreateBuilder() : GenreCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param GenreUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `genres` does not have
     */
    public static function update(?array $input = null): GenreUpdate
    {
        return $input === null ? new GenreUpdate() : GenreUpdate::from($input);
    }
}

/**
 * The canonical create input for `genres`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type GenreCreateShape from Genre
 *
 * @generated from `POST /genres`
 */
final class GenreCreate
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param GenreCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `genres`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if ($member !== 'id' && !\in_array($member, Genre::WRITABLE, true)) {
                throw UnknownAttributeException::for(GenreBase::TYPE, (string) $member, Genre::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            id: $input['id'],
            name: $input['name'],
        );
    }

    /**
     * The `POST /genres` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => GenreBase::TYPE, 'id' => $this->id];

        $attributes = ['name' => $this->name];

        $data['attributes'] = $attributes;

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `genres`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /genres`
 */
final class GenreCreateBuilder
{
    use Conditionable;

    private string|Missing $id = Missing::Value;

    private string|Missing $name = Missing::Value;

    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): GenreCreate
    {
        if ($this->id instanceof Missing) {
            throw MissingRequiredMemberException::for(GenreBase::TYPE, ['id']);
        }

        if ($this->name instanceof Missing) {
            throw MissingRequiredMemberException::for(GenreBase::TYPE, ['name']);
        }

        return new GenreCreate(
            id: $this->id,
            name: $this->name,
        );
    }
}

/**
 * The canonical update input for `genres`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type GenreUpdateShape from Genre
 *
 * @generated from `PATCH /genres/{id}`
 */
final class GenreUpdate
{
    use ImmutableConditionable;

    /**
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $name = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function name(string $name): self
    {
        return new self(
            name: $name,
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
     * @param GenreUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `genres`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Genre::WRITABLE, true)) {
                throw UnknownAttributeException::for(GenreBase::TYPE, (string) $member, Genre::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: \array_key_exists('name', $input) ? $input['name'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /genres/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => GenreBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->name instanceof Missing) {
            $attributes['name'] = $this->name;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        return ['data' => $data];
    }
}
