<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

/**
 * The `@throws` list of one generated method, built from the error statuses its operation
 * declares.
 *
 * Each status maps to the runtime class `JsonApiErrorResponse::for()` throws for it, so the list
 * names exactly what a caller can catch. Every method that reaches the wire can also fail with no
 * response at all, and one that needs a primary resource back can find none, so those two follow
 * the statuses.
 */
final class Throws
{
    /** @var array<int, string> status => runtime exception class */
    private const array CLASSES = [
        400 => 'BadRequest',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'NotFound',
        406 => 'NotAcceptable',
        409 => 'Conflict',
        415 => 'UnsupportedMediaType',
        422 => 'ValidationFailed',
        429 => 'TooManyRequests',
    ];

    /** @var array<string, string> exception class => default reason */
    private const array REASONS = [
        'BadRequest' => 'when the request is malformed',
        'Unauthorized' => 'when credentials are missing or invalid',
        'Forbidden' => 'when the request is not permitted',
        'NotFound' => 'when the endpoint does not exist',
        'NotAcceptable' => 'when the negotiated media type cannot be satisfied',
        'Conflict' => 'when the document conflicts with the endpoint',
        'UnsupportedMediaType' => 'when the `Content-Type` is refused',
        'ValidationFailed' => 'when the document fails validation',
        'TooManyRequests' => 'when the client is rate limited',
        'ServerError' => 'when the server fails',
        'UnexpectedResponse' => 'when the server answers with a status no other class covers',
        'TransportException' => 'when no response is received at all',
        'MalformedDocument' => 'when the response carries no primary resource',
    ];

    /**
     * The aligned tag lines, recording every class named in `$imports`.
     *
     * @param list<int>             $statuses sorted ascending
     * @param array<string, string> $reasons  exception class => reason, over the defaults
     *
     * @return list<string>
     */
    public static function tags(array $statuses, Imports $imports, array $reasons = [], bool $malformed = false): array
    {
        $classes = [];
        foreach ($statuses as $status) {
            $classes[] = self::CLASSES[$status] ?? ($status >= 500 ? 'ServerError' : 'UnexpectedResponse');
        }
        $classes[] = 'TransportException';
        if ($malformed) {
            $classes[] = 'MalformedDocument';
        }
        $classes = \array_values(\array_unique($classes));

        $width = \max(\array_map(\strlen(...), $classes));
        $lines = [];
        foreach ($classes as $class) {
            $imports->runtime('Exceptions\\' . $class);
            $lines[] = '@throws ' . \str_pad($class, $width) . ' ' . ($reasons[$class] ?? self::REASONS[$class]);
        }

        return $lines;
    }
}
