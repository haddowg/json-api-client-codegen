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
