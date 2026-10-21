<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

/**
 * A structured filter value's array shape, declared once as a `@phpstan-type` on the resource
 * class and shared by every filter whose value has exactly that shape.
 *
 * `$range` marks a `{min, max}` bound pair, whose builder parameter reads as `$range`.
 */
final class ShapeAlias
{
    /**
     * @param list<string> $members the shape's member names, in the order the shape declares them
     * @param list<string> $aliases other aliases `$shape` refers to
     */
    public function __construct(
        public readonly string $name,
        public readonly string $shape,
        public readonly array $members,
        public readonly bool $range,
        public readonly array $aliases = [],
    ) {}
}
