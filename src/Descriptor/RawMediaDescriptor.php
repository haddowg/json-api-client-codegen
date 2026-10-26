<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One media type other than JSON:API that a custom action answers with or accepts, from contract
 * 10: a CSV export, an image, a JSON summary.
 *
 * `$kind` decides the PHP type. A `format: binary` schema is binary; a JSON media type is JSON,
 * typed by `$schema`; a string schema or a `text/*` type without one is text; anything else is
 * binary. `$component` names the schema component a `$ref` pointed at, which a generated value
 * class takes its name from.
 */
final class RawMediaDescriptor
{
    public function __construct(
        public readonly string $mediaType,
        public readonly RawMediaKind $kind,
        public readonly ?ValueSchema $schema = null,
        public readonly ?string $component = null,
    ) {}

    /** Whether the media type is a range such as `image/*` rather than one concrete type. */
    public function wildcard(): bool
    {
        return \str_contains($this->mediaType, '*');
    }
}
