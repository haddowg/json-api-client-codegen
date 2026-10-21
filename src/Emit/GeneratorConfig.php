<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * What a generation run is told rather than reads from the document.
 *
 * `$clientName` prefixes the root client class (`MusicCatalog` gives `MusicCatalogClient`).
 */
final class GeneratorConfig
{
    public function __construct(
        public readonly string $namespace,
        public readonly string $clientName,
        public readonly PhpTarget $target,
    ) {
        if (\preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $namespace) !== 1) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a PHP namespace.', $namespace));
        }
        if (\preg_match('/^[A-Z][A-Za-z0-9]*$/', $clientName) !== 1) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a class-name prefix.', $clientName));
        }
    }
}
