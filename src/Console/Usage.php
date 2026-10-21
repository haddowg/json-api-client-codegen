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
            '  ' . self::BINARY . ' <openapi-document> <output-directory> --namespace=<Namespace> [options]',
            '',
            'Options:',
            '  --namespace=<Namespace>  namespace of the generated classes (required)',
            '  --client=<Name>          root client name prefix; defaults to the last namespace segment',
            '  --target=8.3|8.4         PHP version to generate for; defaults to the running PHP',
            '  --check                  write nothing; exit 1 when the output directory is out of date',
            '  --help                   show this message',
            '',
        ];

        return \implode(\PHP_EOL, $lines);
    }
}
