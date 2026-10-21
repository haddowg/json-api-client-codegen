<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Exceptions\UnknownFieldException;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\PageLinks;
use haddowg\JsonApiClient\Pagination\PageNumber;
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
 * The projection surface of `devices`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see DeviceProjection} and {@see DeviceQuery}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `devices` read operations
 */
trait DeviceProjecting
{
    /**
     * Sparse fieldsets, with the `devices` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[devices]` names a member `devices` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[DeviceBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, Device::FIELDS, true)) {
                throw UnknownFieldException::for(DeviceBase::TYPE, $field, Device::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `devices` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.DevicesResource`
 */
abstract class DeviceBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'devices';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `label`
     */
    public function label(): string
    {
        return $this->_string('label');
    }
}

/**
 * A `devices` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `devices` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type DeviceCreateShape array{label: string}
 * @phpstan-type DeviceUpdateShape array{label?: string}
 * @phpstan-type DeviceFieldToken 'label'
 *
 * @generated from `components.schemas.DevicesResource`
 */
final class Device extends DeviceBase
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['label'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['label'];

    /**
     * The members `fields[devices]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['label'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `devices` one
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
     * A resource identifier for a `devices` resource that is not in hand, for a relationship write.
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
     * @return DeviceProjection<DeviceBase>
     */
    public static function projection(): DeviceProjection
    {
        return DeviceProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param DeviceCreateShape|null $input
     *
     * @return ($input is null ? DeviceCreateBuilder : DeviceCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `devices` does not have
     */
    public static function create(?array $input = null): DeviceCreateBuilder|DeviceCreate
    {
        return $input === null ? new DeviceCreateBuilder() : DeviceCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param DeviceUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `devices` does not have
     */
    public static function update(?array $input = null): DeviceUpdate
    {
        return $input === null ? new DeviceUpdate() : DeviceUpdate::from($input);
    }
}

/**
 * The canonical create input for `devices`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type DeviceCreateShape from Device
 *
 * @generated from `POST /devices`
 */
final class DeviceCreate
{
    public function __construct(
        public readonly string $label,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param DeviceCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `devices`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Device::WRITABLE, true)) {
                throw UnknownAttributeException::for(DeviceBase::TYPE, (string) $member, Device::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            label: $input['label'],
        );
    }

    /**
     * The `POST /devices` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => DeviceBase::TYPE];

        $attributes = ['label' => $this->label];

        $data['attributes'] = $attributes;

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `devices`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /devices`
 */
final class DeviceCreateBuilder
{
    use Conditionable;

    private string|Missing $label = Missing::Value;

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): DeviceCreate
    {
        if ($this->label instanceof Missing) {
            throw MissingRequiredMemberException::for(DeviceBase::TYPE, ['label']);
        }

        return new DeviceCreate(
            label: $this->label,
        );
    }
}

/**
 * What a response carrying `devices` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /devices/{id}`, `POST /devices`, `PATCH /devices/{id}`
 */
final class DeviceProjection extends Projection
{
    /** @use DeviceProjecting<TProj> */
    use DeviceProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<DeviceBase>
     */
    public static function make(): self
    {
        /** @var self<DeviceBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<DeviceBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /devices`: the projection, plus everything only a read can carry.
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
 * @generated from `GET /devices`
 */
final class DeviceQuery extends Query
{
    /** @use DeviceProjecting<TProj> */
    use DeviceProjecting;
    use QueryConditionable;

    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * @param positive-int      $number
     * @param positive-int|null $size
     *
     * @return static
     */
    public function page(int $number, ?int $size = null): static
    {
        return $this->paging($size === null ? ['number' => $number] : ['number' => $number, 'size' => $size]);
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
     * The first device the read matches, fetching a single-resource page rather than a whole one.
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

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/devices'));
    }

    /**
     * @return PaginatedCollection<TProj, PageNumber>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $devices = [];

        foreach ($context->primaryList() as $raw) {
            $devices[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $devices,
            PageNumber::read($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `Device`, but `TProj`
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
        return $context->hydrate($raw, Device::_hydrate(...));
    }
}

/**
 * The canonical update input for `devices`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type DeviceUpdateShape from Device
 *
 * @generated from `PATCH /devices/{id}`
 */
final class DeviceUpdate
{
    use ImmutableConditionable;

    /**
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $label = Missing::Value,
    ) {}

    /**
     * @phpstan-pure
     */
    public function label(string $label): self
    {
        return new self(
            label: $label,
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
     * @param DeviceUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `devices`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, Device::WRITABLE, true)) {
                throw UnknownAttributeException::for(DeviceBase::TYPE, (string) $member, Device::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            label: \array_key_exists('label', $input) ? $input['label'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /devices/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => DeviceBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->label instanceof Missing) {
            $attributes['label'] = $this->label;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        return ['data' => $data];
    }
}
