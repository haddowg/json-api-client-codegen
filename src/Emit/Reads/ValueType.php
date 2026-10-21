<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

/**
 * A filter value's type, as the PHPDoc type that checks it and the native type that declares it.
 *
 * The two differ wherever PHP's native types are coarser than the schema: a `list<string>` is a
 * native `array`, and a date bound is `\DateTimeImmutable|string` whichever alias spells it.
 * `$aliases` are the `@phpstan-type` aliases `$doc` names, so a class using this type knows what
 * it has to import.
 */
final class ValueType
{
    /**
     * @param list<string> $aliases
     */
    public function __construct(
        public readonly string $doc,
        public readonly string $native,
        public readonly array $aliases = [],
    ) {}

    public static function mixed(): self
    {
        return new self('mixed', 'mixed');
    }

    /** Whether the native declaration alone says everything, so no `@param` is needed. */
    public function nativeSuffices(): bool
    {
        return $this->doc === $this->native;
    }
}
