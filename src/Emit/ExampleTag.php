<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

use haddowg\JsonApiCodegen\Descriptor\OperationExamples;

/**
 * The `@example` that closes a generated operation method's docblock: the request body example
 * when the operation has one, else its first success response's, as indented, pretty-printed JSON.
 *
 * A collection response with its links runs to a hundred lines or more, which buries the method
 * it documents, so an example longer than {@see MAX_LINES} is left out of the docblock. The
 * descriptor still carries it whole.
 *
 * Each line goes through {@see Php::docblock()}, which puts a backslash between a `*` and the `/`
 * after it. In JSON that is an escaped slash, so the example stays valid JSON and a string value
 * can never close the comment.
 */
final class ExampleTag
{
    public const int MAX_LINES = 60;

    /**
     * The tag's lines, led by a blank one, or none when there is nothing short enough to show.
     *
     * @return list<string>
     */
    public static function lines(?OperationExamples $examples): array
    {
        $primary = $examples?->primary();
        if ($primary === null) {
            return [];
        }

        $json = \json_encode(
            $primary['example'],
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR,
        );
        $json = \explode("\n", $json);
        if (\count($json) > self::MAX_LINES) {
            return [];
        }

        return ['', '@example ' . $primary['label'] . ':', ...\array_map(static fn(string $line): string => '    ' . $line, $json)];
    }
}
