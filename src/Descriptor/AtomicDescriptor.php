<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The server-level Atomic Operations capability: the one endpoint whose request body declares
 * the atomic ext media type. Server-level rather than per-type, and absent when the server
 * exposes no such endpoint — which is what makes `atomic()` an absent method rather than a
 * method that throws.
 */
final class AtomicDescriptor
{
    public function __construct(
        public readonly string $path,
        public readonly string $mediaType,
    ) {}
}
