<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * The PHP version generated code is written for.
 *
 * Method accessors are always emitted and carry all behaviour. An 8.4 target additionally emits
 * get-only property hooks that delegate to them, so the two forms cannot drift.
 */
enum PhpTarget: string
{
    case Php83 = '8.3';
    case Php84 = '8.4';

    /** The target matching the running PHP, so a plain run emits what the project can execute. */
    public static function running(): self
    {
        return \PHP_VERSION_ID >= 80400 ? self::Php84 : self::Php83;
    }

    public function hasPropertyHooks(): bool
    {
        return $this === self::Php84;
    }
}
