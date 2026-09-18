<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One `filter[…]` key a collection read accepts.
 *
 * `$schema` is null when the document declares the filter with an empty schema (`{}`), which
 * most of them currently do — the filter's value type is then genuinely unknown and the
 * generated parameter is `mixed`. It is not a licence to guess one from the field it filters.
 */
final class FilterDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly ?ValueSchema $schema,
    ) {}
}
