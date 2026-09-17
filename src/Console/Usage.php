<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Console;

/**
 * Usage banner printed by bin/json-api-client-codegen.
 */
final class Usage
{
    public const string BINARY = 'json-api-client-codegen';

    public static function text(): string
    {
        $lines = [
            self::BINARY . ' generates a typed PHP JSON:API client from an OpenAPI 3.1 document.',
            '',
            'Usage:',
            '  ' . self::BINARY . ' <openapi-document> <output-directory>',
            '',
            'No generator is wired up yet, so this build only reports its own usage.',
            '',
        ];

        return \implode(\PHP_EOL, $lines);
    }
}
