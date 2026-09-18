<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The whole in-memory model of one service, built from its OpenAPI document and consumed by
 * every emitter.
 *
 * `$contract` is `info.x-generator.contract` where the document declares it, and null where it
 * does not — no document emits it yet, and the absence is not a failure.
 */
final class ApiDescriptor
{
    /**
     * @param array<string, ResourceDescriptor> $resources keyed and sorted by JSON:API type
     * @param array<string, EnumDescriptor>     $enums     keyed and sorted by component name
     */
    public function __construct(
        public readonly array $resources,
        public readonly array $enums,
        public readonly ?AtomicDescriptor $atomic,
        public readonly ?int $contract,
    ) {}

    public function resource(string $type): ?ResourceDescriptor
    {
        return $this->resources[$type] ?? null;
    }

    public function enum(string $name): ?EnumDescriptor
    {
        return $this->enums[$name] ?? null;
    }
}
