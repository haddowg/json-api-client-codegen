<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One media type other than JSON:API that a custom action answers with or accepts, from contract
 * 10: a CSV export, an image, a JSON summary.
 *
 * `$kind` decides the PHP type. A `format: binary` schema is binary; a JSON media type is JSON,
 * typed by `$schema`; a string schema or a `text/*` type without one is text; anything else is
 * binary. A `multipart/form-data` or `application/x-www-form-urlencoded` request body is a form,
 * whatever its schema says. `$component` names the schema component a `$ref` pointed at, which a generated value
 * class takes its name from.
 *
 * A form keeps its schema only when it lists `properties`, which the generated field array is
 * narrowed to. `$additional` then says what other fields it takes: none (`false`, also when the
 * schema is silent), any (`true`), or fields of the schema given.
 */
final class RawMediaDescriptor
{
    public function __construct(
        public readonly string $mediaType,
        public readonly RawMediaKind $kind,
        public readonly ?ValueSchema $schema = null,
        public readonly ?string $component = null,
        public readonly ValueSchema|bool $additional = false,
    ) {}

    /** Whether the media type is a range such as `image/*` rather than one concrete type. */
    public function wildcard(): bool
    {
        return \str_contains($this->mediaType, '*');
    }
}
