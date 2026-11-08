<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ErrorCodeDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;

/**
 * The `@throws` list of one generated method, built from the error statuses its operation
 * declares.
 *
 * Each status maps to the runtime class `JsonApiErrorResponse::for()` throws for it, so the list
 * names exactly what a caller can catch. A code scoped to the operation adds its generated class,
 * which the runtime throws for that code. A collection action taking filters checks their names
 * before it sends anything. Every method that reaches the wire can also fail with no
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
        'UnknownFilterException' => 'when a filter name is not one the action takes',
    ];

    /**
     * {@see tags()} for one operation: its declared statuses and the codes scoped to it.
     *
     * @param array<string, string> $reasons exception class => reason, over the defaults
     *
     * @return list<string>
     */
    public static function forOperation(OperationDescriptor|ActionDescriptor $operation, Imports $imports, array $reasons = [], bool $malformed = false): array
    {
        return self::tags($operation->errorStatuses, $imports, $reasons, $malformed, $operation->scopedErrorCodes, $operation instanceof ActionDescriptor && $operation->filterable !== []);
    }

    /**
     * The aligned tag lines, recording every class named in `$imports`.
     *
     * @param list<int>             $statuses    sorted ascending
     * @param array<string, string> $reasons     exception class => reason, over the defaults
     * @param list<string>          $scopedCodes the error codes scoped to the operation, whose generated classes it can throw
     * @param bool                  $filters     whether the method checks filter names before it sends
     *
     * @return list<string>
     */
    public static function tags(array $statuses, Imports $imports, array $reasons = [], bool $malformed = false, array $scopedCodes = [], bool $filters = false): array
    {
        $classes = $filters ? ['UnknownFilterException'] : [];
        foreach ($statuses as $status) {
            $classes[] = self::CLASSES[$status] ?? ($status >= 500 ? 'ServerError' : 'UnexpectedResponse');
        }
        $generated = [];
        foreach ($scopedCodes as $code) {
            $class = ErrorCodeDescriptor::className($code);
            $generated[$class] = 'when the server answers `' . $code . '`';
            $classes[] = $class;
        }
        $classes[] = 'TransportException';
        if ($malformed) {
            $classes[] = 'MalformedDocument';
        }
        $classes = \array_values(\array_unique($classes));

        $width = \max(\array_map(\strlen(...), $classes));
        $lines = [];
        foreach ($classes as $class) {
            if (isset($generated[$class])) {
                $lines[] = '@throws ' . \str_pad($class, $width) . ' ' . $generated[$class];

                continue;
            }
            $imports->runtime('Exceptions\\' . $class);
            $lines[] = '@throws ' . \str_pad($class, $width) . ' ' . ($reasons[$class] ?? self::REASONS[$class]);
        }

        return $lines;
    }
}
