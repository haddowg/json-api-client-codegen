<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * A family of sort keys an endpoint matches by prefix rather than listing: any key that is
 * `$prefix`, a dot, then a suffix matching `$suffixPattern`, in either direction. Read from the
 * sort parameter's `x-sort-families`.
 */
final class SortFamilyDescriptor
{
    public function __construct(
        public readonly string $prefix,
        public readonly string $suffixPattern,
        public readonly bool $deprecated = false,
    ) {}

    /** The PCRE pattern a signed token of this family matches, as the sort parameter's `anyOf` branch does. */
    public function pattern(): string
    {
        return '~^-?' . \preg_quote($this->prefix, '~') . '\.(?:' . \str_replace('~', '\~', $this->suffixPattern) . ')$~';
    }
}
