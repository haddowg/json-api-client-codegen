<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One operation the document advertises: its path template, its method, and the error statuses
 * it declares.
 *
 * The statuses are what every generated method's `@throws` list is built from, so they are read
 * per operation rather than assumed uniform. `$examples` holds the worked examples a contract-12
 * document carries for it, and is null when the operation has none.
 *
 * `$scopes` lists the OAuth scopes a contract-16 document says the operation needs, in the order
 * its `security` names them. It is null when the operation needs none, never an empty list.
 */
final class OperationDescriptor
{
    /**
     * @param list<int>         $errorStatuses sorted ascending
     * @param list<string>|null $scopes
     * @param list<string>      $scopedErrorCodes the error codes scoped to this operation, which it raises beside the ones every operation does, sorted
     */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly array $errorStatuses,
        public readonly ?ProjectionDescriptor $projection,
        public readonly ?OperationExamples $examples = null,
        public readonly ?array $scopes = null,
        public readonly bool $deprecated = false,
        public readonly array $scopedErrorCodes = [],
    ) {}
}
