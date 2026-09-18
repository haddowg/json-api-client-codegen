<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The `withCount` capability of one read endpoint (the Countable profile).
 *
 * `$tokens` are the count tokens the endpoint accepts — `_self_` counts the collection itself,
 * a relation name counts that relation per item. `$profile` is the URI a client must negotiate
 * in `Accept` before the server honours `withCount`; without it the parameter is rejected under
 * strict query validation. It is read from the parameter's `x-profile`, never hardcoded.
 */
final class CountableDescriptor
{
    /** @param list<string> $tokens */
    public function __construct(
        public readonly array $tokens,
        public readonly string $profile,
    ) {}
}
