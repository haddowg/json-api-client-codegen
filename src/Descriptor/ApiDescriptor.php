<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The whole in-memory model of one service, built from its OpenAPI document and consumed by
 * every emitter.
 *
 * `$contract` is `info.x-generator.contract` where the document declares it, and null where it
 * does not. Documents from `json-api` before 1.1.0 carry none, and the absence is not a failure.
 *
 * `$errors` is the document's error catalogue. It is open: a code missing from it is still a
 * valid error and must fall back, never fail.
 *
 * `$warnings` are what the builder read around rather than generated, such as a pagination
 * strategy it does not know. They reach the user through {@see \haddowg\JsonApiCodegen\Emit\Generator::warnings()}.
 */
final class ApiDescriptor
{
    /**
     * @param array<string, ResourceDescriptor> $resources keyed and sorted by JSON:API type
     * @param array<string, EnumDescriptor>     $enums     keyed and sorted by component name
     * @param array<string, ErrorCodeDescriptor> $errors  keyed and sorted by error code
     * @param list<string>                      $warnings
     */
    public function __construct(
        public readonly array $resources,
        public readonly array $enums,
        public readonly ?AtomicDescriptor $atomic,
        public readonly ?int $contract,
        public readonly array $errors,
        public readonly array $warnings = [],
    ) {}

    public function resource(string $type): ?ResourceDescriptor
    {
        return $this->resources[$type] ?? null;
    }

    public function enum(string $name): ?EnumDescriptor
    {
        return $this->enums[$name] ?? null;
    }

    public function error(string $code): ?ErrorCodeDescriptor
    {
        return $this->errors[$code] ?? null;
    }
}
