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
 */
final class AttributeDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly string $format,
        public readonly ?string $enum,
    ) {}
}
