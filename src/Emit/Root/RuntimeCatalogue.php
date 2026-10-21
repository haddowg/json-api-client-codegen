<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Root;

/**
 * The error codes the installed `haddowg/json-api-client` already ships a class for.
 *
 * Read from the runtime itself rather than from a copy kept here, because the runtime installed
 * beside the codegen is the one the generated client will run against: a code it ships needs no
 * generated class, and a code it lacks does. A copy would drift against both. The generated
 * client cannot run without the runtime, so a project generating one always has it installed.
 */
final class RuntimeCatalogue
{
    public const string CLASS_NAME = 'haddowg\\JsonApiClient\\Exceptions\\Codes\\ErrorCodes';

    /**
     * @return list<string>
     *
     * @throws RuntimeCatalogueUnavailable when the runtime's code map cannot be loaded
     */
    public static function codes(string $class = self::CLASS_NAME): array
    {
        if (!\class_exists($class) || !\defined($class . '::MAP')) {
            throw RuntimeCatalogueUnavailable::missing($class);
        }

        $map = \constant($class . '::MAP');
        if (!\is_array($map)) {
            throw RuntimeCatalogueUnavailable::malformed($class);
        }

        $codes = [];
        foreach (\array_keys($map) as $code) {
            $codes[] = (string) $code;
        }

        return $codes;
    }
}
