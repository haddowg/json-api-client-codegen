<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

use haddowg\JsonApiCodegen\Emit\ClassUnit;

/**
 * How one attribute is read: the accessor's name and types, its body, and whatever it needs
 * declared elsewhere — a shape alias on the resource class, or the branch classes of a tagged
 * union.
 */
final class AttributeRead
{
    /**
     * @param string|null     $docType  the PHPDoc type, where the native one says less
     * @param list<string>    $body     the accessor's statements, unindented
     * @param list<string>    $prose    the accessor's docblock summary lines
     * @param list<string>    $imports
     * @param list<ClassUnit> $classes
     */
    public function __construct(
        public readonly string $name,
        public readonly string $method,
        public readonly string $nativeType,
        public readonly ?string $docType,
        public readonly array $body,
        public readonly array $prose = [],
        public readonly ?string $alias = null,
        public readonly ?string $aliasType = null,
        public readonly array $imports = [],
        public readonly array $classes = [],
    ) {}
}
