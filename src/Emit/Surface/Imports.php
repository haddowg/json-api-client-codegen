<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

/**
 * The runtime classes one generated declaration refers to, collected as its source is written.
 */
final class Imports
{
    public const string RUNTIME = 'haddowg\\JsonApiClient\\';

    /** @var array<string, true> */
    private array $names = [];

    /** Record a runtime class by its name under `haddowg\JsonApiClient\` and return its short name. */
    public function runtime(string $relative): string
    {
        return $this->add(self::RUNTIME . $relative);
    }

    /** Record a fully-qualified class and return its short name. */
    public function add(string $fqcn): string
    {
        $this->names[$fqcn] = true;
        $at = \strrpos($fqcn, '\\');

        return $at === false ? $fqcn : \substr($fqcn, $at + 1);
    }

    /** @return list<string> */
    public function all(): array
    {
        $names = \array_keys($this->names);
        \sort($names, \SORT_STRING);

        return $names;
    }
}
