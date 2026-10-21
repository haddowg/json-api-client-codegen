<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

/**
 * One `filter[…]` key as the query builder exposes it.
 *
 * `$method` is null when the filter's name normalises to a method another filter also claims and
 * no unambiguous fallback exists. The filter is then reachable through `filter()` and
 * `filterRaw()` only, which is better than a `whereX()` that means one of two filters.
 *
 * `$shape` is the `@phpstan-type` alias a structured value is declared under, null for a scalar.
 */
final class FilterSlot
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $method,
        public readonly ValueType $type,
        public readonly ?ShapeAlias $shape,
    ) {}
}
