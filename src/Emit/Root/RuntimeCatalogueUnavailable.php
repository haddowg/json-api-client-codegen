<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Root;

/**
 * Generation cannot tell which of the document's error codes the runtime already covers.
 *
 * Guessing either way is wrong: assuming none are covered duplicates every core code class, and
 * assuming all are drops the API's own codes without a word. So generation stops and says how to
 * fix it.
 */
final class RuntimeCatalogueUnavailable extends \RuntimeException
{
    public static function missing(string $class): self
    {
        return new self(\sprintf(
            'Cannot read the error codes the runtime ships: %s is not loadable. Install haddowg/json-api-client beside the codegen (the generated client requires it), then generate again.',
            $class,
        ));
    }

    public static function malformed(string $class): self
    {
        return new self(\sprintf('%s::MAP is not an array of error codes. Is haddowg/json-api-client a supported version?', $class));
    }
}
