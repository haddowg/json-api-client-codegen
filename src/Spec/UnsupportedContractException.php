<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Spec;

/**
 * The document declares a contract older than this codegen can read.
 */
final class UnsupportedContractException extends \RuntimeException
{
    public function __construct(
        public readonly int $contract,
        public readonly int $minimum,
        public readonly int $maximum,
    ) {
        parent::__construct(\sprintf(
            'the document declares contract %d, which predates what this codegen reads (it supports %d to %d). Use an older codegen release, or regenerate the document from a newer server.',
            $contract,
            $minimum,
            $maximum,
        ));
    }
}
