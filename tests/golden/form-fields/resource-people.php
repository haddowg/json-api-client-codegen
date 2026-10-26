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
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\Request;
use haddowg\JsonApiClient\Pagination\Cursor;
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
use haddowg\JsonApiClient\Support\Coerce;
use haddowg\JsonApiClient\Support\Conditionable;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiClient\Support\ImmutableConditionable;
use haddowg\JsonApiClient\Support\Missing;
use haddowg\JsonApiClient\Support\QueryConditionable;

/**
 * The projection surface of `people`: the `withX()` include methods and the checked `fields()`,
 * shared by {@see PeopleProjection}.
 *
 * A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>`
 * over a `static`-returning primitive resolves at level 9 only in a final class.
 *
 * @template TProj of object
 *
 * @phpstan-require-extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of the `people` read operations
 */
trait PeopleProjecting
{
    /**
     * Sparse fieldsets, with the `people` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[people]` names a member `people` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[PeopleBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, People::FIELDS, true)) {
                throw UnknownFieldException::for(PeopleBase::TYPE, $field, People::FIELDS);
            }
        }

        return parent::fields($fields);
    }
}

/**
 * A `people` resource with no relation hydrated: everything a read is guaranteed to give you.
 *
 * Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response
 * did not carry throws rather than returning null, so an excluded attribute never reads as an empty
 * one.
 *
 * @generated from `components.schemas.PeopleResource`
 */
abstract class PeopleBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'people';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    /** @var non-empty-string */
    public string $name { get => $this->name(); }

    public ?Status $role { get => $this->role(); }

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
     * @throws FieldNotSelectedException when a sparse fieldset excluded `name`
     */
    public function name(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('name');

        return $value;
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `role`
     */
    public function role(): ?Status
    {
        return $this->_nullableEnum('role', Status::class);
    }

    /**
     * The `company` linkage, without hydrating anything. Never throws.
     */
    public function companyRef(): ?Identifier
    {
        return $this->_relationship('company', true)->identifier();
    }

    /**
     * Whether the `company` linkage points at anything. Never throws.
     */
    public function hasCompany(): bool
    {
        return !$this->_relationship('company', true)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'company' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, true);
    }
}

/**
 * A `people` resource as it actually arrives: every attribute, and every relation accessor.
 *
 * The runtime value is always this class. A projection is a static narrowing over it, so a relation
 * accessor exists at runtime whether or not it type-checks, and calling it without the include
 * throws.
 *
 * It also carries every array shape the `people` surface accepts, and the descriptor constants the
 * runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using
 * one imports it from here.
 *
 * @phpstan-type PeopleRoleInput Status|'archived'|'draft'|'published'
 * @phpstan-type PeopleCompanyInput Identifier|null
 * @phpstan-type PeopleCreateShape array{id?: string|null, name: non-empty-string, role?: PeopleRoleInput, company?: PeopleCompanyInput}
 * @phpstan-type PeopleUpdateShape array{name?: non-empty-string, role?: PeopleRoleInput, company?: PeopleCompanyInput}
 * @phpstan-type PeopleFilterShape array{name?: string}
 * @phpstan-type PeopleFieldToken 'company'|'name'|'role'
 *
 * @generated from `components.schemas.PeopleResource`
 */
final class People extends PeopleBase
{
    /**
     * Every member a write input accepts, in document order. The runtime guard on the array door
     * checks against this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array WRITABLE = ['name', 'role', 'company'];

    /**
     * Members a create document requires.
     *
     * @var list<string>
     */
    public const array REQUIRED_ON_CREATE = ['name'];

    /**
     * The filters `GET /people` accepts. The runtime guard on both array doors checks names against
     * this, and the did-you-mean is drawn from it.
     *
     * @var list<string>
     */
    public const array FILTERS = ['name'];

    /**
     * The members `fields[people]` may name.
     *
     * @var list<string>
     */
    public const array FIELDS = ['name', 'role', 'company'];

    /**
     * Build a resource from one resource object of an indexed response.
     *
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `people` one
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
     * A resource identifier for a `people` resource that is not in hand, for a relationship write.
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
     * @return PeopleProjection<PeopleBase>
     */
    public static function projection(): PeopleProjection
    {
        return PeopleProjection::make();
    }

    /**
     * The create input: a builder when called with nothing, or the create DTO built from an array
     * whose keys are checked against the descriptor.
     *
     * @param PeopleCreateShape|null $input
     *
     * @return ($input is null ? PeopleCreateBuilder : PeopleCreate)
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `people` does not have
     */
    public static function create(?array $input = null): PeopleCreateBuilder|PeopleCreate
    {
        return $input === null ? new PeopleCreateBuilder() : PeopleCreate::from($input);
    }

    /**
     * The update input: an empty one to assemble fluently, or one built from an array whose keys
     * are checked against the descriptor.
     *
     * @param PeopleUpdateShape|null $input
     *
     * @throws \haddowg\JsonApiClient\Exceptions\UnknownAttributeException when the array names a member `people` does not have
     */
    public static function update(?array $input = null): PeopleUpdate
    {
        return $input === null ? new PeopleUpdate() : PeopleUpdate::from($input);
    }
}

/**
 * The `people` type: every operation its collection endpoint advertises, and the way to one
 * resource by id.
 *
 * @phpstan-import-type PeopleCreateShape from People
 *
 * @generated from the `people` operations
 */
final class PeopleAccessor
{
    public function __construct(private readonly Fetcher $fetcher) {}

    /**
     * A read of the people collection, not yet sent: unprojected, unfiltered and unsorted. Until a
     * picker chooses a strategy, it reads the server's default, `page`.
     *
     * @return PeopleQuery<PeopleBase, PageNumber>
     */
    public function query(): PeopleQuery
    {
        /** @var PeopleQuery<PeopleBase, PageNumber> */
        return new PeopleQuery($this->fetcher, PageNumber::read(...));
    }

    /**
     * A handle on one people by id. Nothing is fetched until a method on it is called.
     */
    public function id(string $id): PeopleHandle
    {
        return new PeopleHandle($this->fetcher, $id);
    }

    /**
     * Create a people, in any of the three input forms, and return it shaped by the optional
     * projection: a closure over a fresh projection, or a prebuilt one.
     *
     * The document accepts a client-generated id, carried as the input's `id`.
     *
     * @template TProj of object
     *
     * @param PeopleCreate|PeopleCreateBuilder|PeopleCreateShape                                             $input
     * @param (callable(PeopleProjection<PeopleBase>): PeopleProjection<TProj>)|PeopleProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PeopleBase : PeopleBase&TProj)
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
        PeopleCreate|PeopleCreateBuilder|array $input,
        callable|PeopleProjection|null $projection = null,
    ): PeopleBase {
        $document = match (true) {
            $input instanceof PeopleCreate => $input,
            $input instanceof PeopleCreateBuilder => $input->build(),
            default => PeopleCreate::from($input),
        };

        $query = PeopleProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::post($query->appendTo('/people'), \json_encode($document->toDocument(), \JSON_THROW_ON_ERROR)),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The create response', 'data');

        /** @var PeopleBase&TProj */
        return $context->hydrate($raw, People::_hydrate(...));
    }
}

/**
 * The `company` relationship of one people. `get()` reads the linkage; any read the server switched
 * off is absent. The writes are `set()`, the methods the relationship endpoint declares, and no
 * others.
 *
 * @generated from `/people/{id}/relationships/company`
 */
final class PeopleCompanyRelation
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
     * @throws NotFound           when no people has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function get(): ?Identifier
    {
        $raw = $this->fetcher
            ->read(Request::get('/people/' . $this->id . '/relationships/company'))
            ->primary();

        return $raw === null ? null : Identifier::fromArray($raw, 'the "company" linkage');
    }

    /**
     * Point the relationship at another resource, or at nothing.
     *
     * @throws BadRequest           when the document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the write is not permitted
     * @throws NotFound             when no people has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the linkage conflicts with the relationship
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the linkage fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     */
    public function set(Identifier|null $target): void
    {
        $this->fetcher->send(Request::patch(
            '/people/' . $this->id . '/relationships/company',
            \json_encode(['data' => Coerce::linkage($target)], \JSON_THROW_ON_ERROR),
        ));
    }
}

/**
 * The canonical create input for `people`, as named arguments. A missing required member is a
 * static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which
 * keeps an absent member distinct from an explicit `null`.
 *
 * @phpstan-import-type PeopleCreateShape from People
 *
 * @generated from `POST /people`
 */
final class PeopleCreate
{
    /**
     * @param non-empty-string $name
     * @param Identifier|Missing|null $company
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly Status|Missing $role = Missing::Value,
        public readonly Identifier|Missing|null $company = Missing::Value,
    ) {}

    /**
     * Build the DTO from the array door, checking every key against the descriptor.
     *
     * @param PeopleCreateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `people`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if ($member !== 'id' && !\in_array($member, People::WRITABLE, true)) {
                throw UnknownAttributeException::for(PeopleBase::TYPE, (string) $member, People::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: $input['name'],
            id: \array_key_exists('id', $input) ? $input['id'] : null,
            role: Coerce::enum(Status::class, \array_key_exists('role', $input) ? $input['role'] : Missing::Value),
            company: \array_key_exists('company', $input) ? $input['company'] : Missing::Value,
        );
    }

    /**
     * The `POST /people` request document.
     *
     * @return array<string, mixed>
     */
    public function toDocument(): array
    {
        $data = ['type' => PeopleBase::TYPE];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        $attributes = ['name' => $this->name];

        if (!$this->role instanceof Missing) {
            $attributes['role'] = $this->role->value;
        }

        $data['attributes'] = $attributes;

        $relationships = [];

        if (!$this->company instanceof Missing) {
            $relationships['company'] = ['data' => Coerce::linkage($this->company)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}

/**
 * The fluent create input for `people`, for assembly that is conditional. `build()` rejects a
 * missing required member, which this form cannot check statically.
 *
 * @generated from `POST /people`
 */
final class PeopleCreateBuilder
{
    use Conditionable;

    private ?string $id = null;

    /**
     * @var non-empty-string|Missing
     */
    private string|Missing $name = Missing::Value;

    private Status|Missing $role = Missing::Value;

    private Identifier|Missing|null $company = Missing::Value;

    public function id(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param non-empty-string $name
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function role(Status $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function company(?Identifier $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Produce the DTO, which is the only form that ever reaches the serialiser.
     *
     * @throws MissingRequiredMemberException when a member the create document requires was never set
     */
    public function build(): PeopleCreate
    {
        if ($this->name instanceof Missing) {
            throw MissingRequiredMemberException::for(PeopleBase::TYPE, ['name']);
        }

        return new PeopleCreate(
            id: $this->id,
            name: $this->name,
            role: $this->role,
            company: $this->company,
        );
    }
}

/**
 * One people by id, before anything has been fetched. Creating the handle performs no IO; each
 * method on it is one request.
 *
 * @phpstan-import-type PeopleUpdateShape from People
 *
 * @generated from the `people` operations on one resource
 */
final class PeopleHandle
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
     * Fetch the people, shaped by the optional projection: a closure over a fresh projection, or a
     * prebuilt one.
     *
     * @template TProj of object
     *
     * @param (callable(PeopleProjection<PeopleBase>): PeopleProjection<TProj>)|PeopleProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PeopleBase : PeopleBase&TProj)
     *
     * @throws BadRequest         when a query parameter is rejected
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the read is not permitted
     * @throws NotFound           when no people has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     * @throws MalformedDocument  when the response carries no primary resource
     */
    public function get(callable|PeopleProjection|null $projection = null): PeopleBase
    {
        $query = PeopleProjection::resolve($projection);
        $context = $this->fetcher->read(Request::get($query->appendTo('/people/' . $this->id)), $query);
        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The people response', 'data');

        /** @var PeopleBase&TProj */
        return $context->hydrate($raw, People::_hydrate(...));
    }

    /**
     * Patch the people, in either input form, and return it shaped by the optional projection.
     *
     * The id comes from the handle and is written into the document, so the body and the URL cannot
     * disagree.
     *
     * @template TProj of object
     *
     * @param PeopleUpdate|PeopleUpdateShape                                                                 $input
     * @param (callable(PeopleProjection<PeopleBase>): PeopleProjection<TProj>)|PeopleProjection<TProj>|null $projection
     *
     * @return ($projection is null ? PeopleBase : PeopleBase&TProj)
     *
     * @throws BadRequest           when the request is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when the update is not permitted
     * @throws NotFound             when no people has this id
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when the document's type or id disagrees with the endpoint
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when the document fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response carries no primary resource
     */
    public function update(
        PeopleUpdate|array $input,
        callable|PeopleProjection|null $projection = null,
    ): PeopleBase {
        $document = match (true) {
            $input instanceof PeopleUpdate => $input,
            default => PeopleUpdate::from($input),
        };

        $query = PeopleProjection::resolve($projection);

        $context = $this->fetcher->read(
            Request::patch(
                $query->appendTo('/people/' . $this->id),
                \json_encode($document->toDocument($this->id), \JSON_THROW_ON_ERROR),
            ),
            $query,
        );

        $raw = $context->primary() ?? throw MalformedDocument::missingMember('The update response', 'data');

        /** @var PeopleBase&TProj */
        return $context->hydrate($raw, People::_hydrate(...));
    }

    /**
     * Delete the people.
     *
     * @throws BadRequest         when the request is malformed
     * @throws Unauthorized       when credentials are missing or invalid
     * @throws Forbidden          when the delete is not permitted
     * @throws NotFound           when no people has this id
     * @throws NotAcceptable      when the negotiated media type cannot be satisfied
     * @throws ServerError        when the server fails
     * @throws TransportException when no response is received at all
     */
    public function delete(): void
    {
        $this->fetcher->send(Request::delete('/people/' . $this->id));
    }

    /**
     * The `company` relationship, a to-one whose endpoint advertises `set()`.
     */
    public function company(): PeopleCompanyRelation
    {
        return new PeopleCompanyRelation($this->fetcher, $this->id);
    }
}

/**
 * What a response carrying `people` should bring back: `include` and `fields`, and nothing else.
 *
 * A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are
 * not here at all. Reusable: build one and hand it to every operation that should come back the
 * same shape.
 *
 * @template TProj of object
 *
 * @extends Projection<TProj>
 *
 * @generated from the `include` and `fields` parameters of `GET /people/{id}`, `POST /people`, `PATCH /people/{id}`
 */
final class PeopleProjection extends Projection
{
    /** @use PeopleProjecting<TProj> */
    use PeopleProjecting;

    /**
     * An empty projection over the unnarrowed resource.
     *
     * @return self<PeopleBase>
     */
    public static function make(): self
    {
        /** @var self<PeopleBase> */
        return new self();
    }

    /**
     * Resolve either projection form, a closure or a prebuilt projection, to the query parameters a
     * response carries.
     *
     * @template T of object
     *
     * @param (callable(self<PeopleBase>): self<T>)|self<T>|null $projection
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
 * A read of `GET /people`: the projection, plus everything only a read can carry.
 *
 * Every door the endpoint advertises is here and nothing it does not. Filters come in three doors:
 * a `whereX()` per filter, the typed `filter()` shape, and the loose `filterRaw()`. They are
 * separate methods because a parameter typed as the shape or any array would accept anything. Both
 * array doors still check names at runtime.
 *
 * The endpoint offers a menu of pagination strategies. `TPage` is the page a read returns: the
 * server's default, `page`, until a `pageByX()` picker chooses another.
 *
 * @template TProj of object
 *
 * @template TPage of Page
 *
 * @extends Query<TProj>
 *
 * @phpstan-import-type PeopleFilterShape from People
 *
 * @generated from `GET /people`
 */
final class PeopleQuery extends Query
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
     * Sparse fieldsets, with the `people` members checked against the descriptor. Other types'
     * fieldsets pass through unchecked.
     *
     * @param array<string, list<string>> $fields
     *
     * @return static
     *
     * @throws UnknownFieldException when `fields[people]` names a member `people` does not have
     */
    public function fields(array $fields): static
    {
        foreach ($fields[PeopleBase::TYPE] ?? [] as $field) {
            if (!\in_array($field, People::FIELDS, true)) {
                throw UnknownFieldException::for(PeopleBase::TYPE, $field, People::FIELDS);
            }
        }

        return parent::fields($fields);
    }

    /**
     * Filter by `name`.
     *
     * @return static
     */
    public function whereName(string $value): static
    {
        return $this->filtering(['name' => $value]);
    }

    /**
     * The typed array door: values are checked statically, nested ones included, and names at
     * runtime.
     *
     * @param PeopleFilterShape $filter
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
     * Page by the `page` strategy, the one the server uses when a read picks none. Sends
     * `page[kind]=page` with whichever members are given, replacing whatever an earlier picker set,
     * and reads `PageNumber` pages. `$number` defaults to 1 and `$size` defaults to 15.
     *
     * @param positive-int|null $number
     * @param int<1, 100>|null  $size
     *
     * @return self<TProj, PageNumber>
     */
    public function pageByPage(?int $number = null, ?int $size = null): self
    {
        return $this->choose(['kind' => 'page', 'number' => $number, 'size' => $size], PageNumber::read(...));
    }

    /**
     * Page by the `cursor` strategy. Sends `page[kind]=cursor` with whichever members are given,
     * replacing whatever an earlier picker set, and reads `Cursor` pages. `$size` defaults to 15.
     *
     * @param string|null      $after
     * @param string|null      $before
     * @param int<1, 100>|null $size
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
     * Paginator parameters assembled at runtime. Public here because `GET /people` is paginated;
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
     * @return PaginatedCollection<TProj, TPage>
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
     * The first people the read matches.
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
        return $this->get()->first();
    }

    /**
     * @param PeopleFilterShape|array<string, mixed> $filter
     *
     * @return array<string, mixed>
     *
     * @throws UnknownFilterException
     */
    private static function guardFilters(array $filter): array
    {
        foreach (\array_keys($filter) as $name) {
            $name = (string) $name;

            if (!\in_array($name, People::FILTERS, true)) {
                throw UnknownFilterException::for($name, People::FILTERS);
            }
        }

        return $filter;
    }

    private function request(ReadQuery $query): Request
    {
        return Request::get($query->appendTo('/people'));
    }

    /**
     * @return PaginatedCollection<TProj, TPage>
     */
    private function collect(ResourceContext $context, ReadQuery $query): PaginatedCollection
    {
        $document = $context->document();
        $people = [];

        foreach ($context->primaryList() as $raw) {
            $people[] = $this->hydrate($raw, $context);
        }

        return new PaginatedCollection(
            $people,
            ($this->reader)($document->pageMeta(), PageLinks::fromArray($document->links())),
            $document->meta(),
            $document->links(),
            fn(string $url): PaginatedCollection => $this->collect($this->fetcher->readUrl($url, $query), $query),
        );
    }

    /**
     * The one assertion the design cannot avoid. The runtime always builds a `People`, but `TProj`
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
        return $context->hydrate($raw, People::_hydrate(...));
    }
}

/**
 * The canonical update input for `people`: every member optional, which is what a JSON:API `PATCH`
 * is. The id is not a member; it comes from the handle the update is called on, so it cannot
 * disagree with the URL.
 *
 * @phpstan-import-type PeopleUpdateShape from People
 *
 * @generated from `PATCH /people/{id}`
 */
final class PeopleUpdate
{
    use ImmutableConditionable;

    /**
     * @param non-empty-string|Missing $name
     * @param Identifier|Missing|null $company
     *
     * @phpstan-pure
     */
    public function __construct(
        public readonly string|Missing $name = Missing::Value,
        public readonly Status|Missing $role = Missing::Value,
        public readonly Identifier|Missing|null $company = Missing::Value,
    ) {}

    /**
     * @param non-empty-string $name
     *
     * @phpstan-pure
     */
    public function name(string $name): self
    {
        return new self(
            name: $name,
            role: $this->role,
            company: $this->company,
        );
    }

    /**
     * @phpstan-pure
     */
    public function role(Status $role): self
    {
        return new self(
            name: $this->name,
            role: $role,
            company: $this->company,
        );
    }

    /**
     * @phpstan-pure
     */
    public function company(?Identifier $company): self
    {
        return new self(
            name: $this->name,
            role: $this->role,
            company: $company,
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
     * @param PeopleUpdateShape $input
     *
     * @throws UnknownAttributeException when a key is not a writable member of `people`
     */
    public static function from(array $input): self
    {
        foreach (\array_keys($input) as $member) {
            if (!\in_array($member, People::WRITABLE, true)) {
                throw UnknownAttributeException::for(PeopleBase::TYPE, (string) $member, People::WRITABLE);
            }
        }

        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.
        return new self(
            name: \array_key_exists('name', $input) ? $input['name'] : Missing::Value,
            role: Coerce::enum(Status::class, \array_key_exists('role', $input) ? $input['role'] : Missing::Value),
            company: \array_key_exists('company', $input) ? $input['company'] : Missing::Value,
        );
    }

    /**
     * The `PATCH /people/{id}` request document. A member holding `null` is sent and clears the
     * server value; one holding `Missing` is not sent.
     *
     * @return array<string, mixed>
     */
    public function toDocument(string $id): array
    {
        $data = ['type' => PeopleBase::TYPE, 'id' => $id];

        $attributes = [];

        if (!$this->name instanceof Missing) {
            $attributes['name'] = $this->name;
        }

        if (!$this->role instanceof Missing) {
            $attributes['role'] = $this->role->value;
        }

        if ($attributes !== []) {
            $data['attributes'] = $attributes;
        }

        $relationships = [];

        if (!$this->company instanceof Missing) {
            $relationships['company'] = ['data' => Coerce::linkage($this->company)];
        }

        if ($relationships !== []) {
            $data['relationships'] = $relationships;
        }

        return ['data' => $data];
    }
}
