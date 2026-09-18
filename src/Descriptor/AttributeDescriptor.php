<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One attribute a resource type declares.
 *
 * `$format` is the wire format hint that drives coercion: an explicit `format` (`date-time`,
 * `date`) when the schema declares one, else the JSON type (`string`, `integer`, `number`,
 * `boolean`, `object`, `array`). `$enum` names the enum component the value is drawn from, and
 * is what lets the emitter reach a generated PHP enum instead of a bare string.
 *
 * `$nullable` is read from the schema admitting `"null"` among its types. It is separate from
 * the format because the emitted type depends on both — `["number", "null"]` is `?float` and
 * `"number"` is `float`, and the hint alone cannot tell them apart.
 */
final class AttributeDescriptor
{
    /**
     * @param ValueSchema|null $schema the whole fragment, carried only where the format hint
     *                                 cannot express the value — an object's members, an array's
     *                                 item type. Null for a scalar, where the format and
     *                                 `$nullable` already say everything.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $format,
        public readonly ?string $enum,
        public readonly bool $nullable,
        public readonly ?ValueSchema $schema,
    ) {}
}
