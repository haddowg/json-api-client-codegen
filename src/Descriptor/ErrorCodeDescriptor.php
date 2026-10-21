<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One code from the document's error catalogue: a named `<Name>Error` schema narrowing `code`
 * and `status` to constants, and possibly requiring one `source` member.
 *
 * The catalogue is open. An error carrying a code not listed here is still a valid error, so
 * nothing generated from these may assume the list is complete.
 */
final class ErrorCodeDescriptor
{
    public function __construct(
        public readonly string $code,
        public readonly int $status,
        public readonly string $schema,
        public readonly string $title,
        public readonly ?ErrorSourceKind $source,
    ) {}
}
