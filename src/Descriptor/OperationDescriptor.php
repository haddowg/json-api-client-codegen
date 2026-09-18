<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One operation the document advertises: its path template, its method, and the error statuses
 * it declares.
 *
 * The statuses are what every generated method's `@throws` list is built from, so they are read
 * per operation rather than assumed uniform.
 */
final class OperationDescriptor
{
    /** @param list<int> $errorStatuses sorted ascending */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly array $errorStatuses,
    ) {}
}
