<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One case of an enumerated value.
 *
 * `$name` comes from `x-enum-varnames` and `$description` from `x-enum-descriptions`, both
 * index-aligned with `enum` in the document and resolved here so nothing downstream has to
 * re-align them. Either is null when the document omits that extension.
 */
final class EnumCase
{
    public function __construct(
        public readonly string $value,
        public readonly ?string $name,
        public readonly ?string $description,
    ) {}
}
