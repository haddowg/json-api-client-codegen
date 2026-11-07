<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\ErrorCodeDescriptor;

/**
 * The `@throws` lines of a read, from the error statuses its operation declares.
 *
 * The classes are the runtime's status exceptions, chosen exactly as
 * `JsonApiErrorResponse::for()` dispatches them, so the list names what can actually be caught.
 * A transport failure is possible on every request and is always listed.
 */
final class Throws
{
    private const string NS = 'haddowg\\JsonApiClient\\Exceptions\\';

    /** @var array<int, array{string, string}> */
    private const array STATUSES = [
        400 => ['BadRequest', 'when a query parameter is rejected'],
        401 => ['Unauthorized', 'when credentials are missing or invalid'],
        403 => ['Forbidden', 'when the read is not permitted'],
        404 => ['NotFound', 'when the endpoint does not exist'],
        406 => ['NotAcceptable', 'when the negotiated media type cannot be satisfied'],
        409 => ['Conflict', 'when the request conflicts with the endpoint'],
        415 => ['UnsupportedMediaType', 'when the `Content-Type` is refused'],
        422 => ['ValidationFailed', 'when the request fails validation'],
        429 => ['TooManyRequests', 'when the server is rate limiting'],
    ];

    /**
     * @param list<int>    $statuses
     * @param list<string> $scopedCodes the error codes scoped to the read, whose generated classes it can throw
     *
     * @return list<array{string, string}> short class name and description, in status order, then the scoped codes
     */
    public static function for(array $statuses, array $scopedCodes = []): array
    {
        $out = [];
        foreach ($statuses as $status) {
            $entry = self::STATUSES[$status] ?? ($status >= 500
                ? ['ServerError', 'when the server fails']
                : ['UnexpectedResponse', 'when the server answers ' . $status]);
            $out[$entry[0]] = $entry;
        }
        foreach ($scopedCodes as $code) {
            $class = ErrorCodeDescriptor::className($code);
            $out[$class] = [$class, 'when the server answers `' . $code . '`'];
        }
        $out['TransportException'] = ['TransportException', 'when no response is received at all'];

        return \array_values($out);
    }

    /**
     * Docblock lines, aligned on the description.
     *
     * @param list<array{string, string}> $throws
     *
     * @return list<string>
     */
    public static function lines(array $throws): array
    {
        $width = 0;
        foreach ($throws as [$class]) {
            $width = \max($width, \strlen($class));
        }

        return \array_map(static fn(array $entry): string => '@throws ' . \str_pad($entry[0], $width) . ' ' . $entry[1], $throws);
    }

    /**
     * @param list<array{string, string}> $throws
     *
     * @return list<string> fully-qualified class names of the runtime classes; a generated code class shares the generated namespace and needs none
     */
    public static function imports(array $throws): array
    {
        $out = [];
        foreach ($throws as [$class]) {
            // A generated code class is named `<Code>Error`; the one runtime class so named is ServerError.
            if ($class === 'ServerError' || !\str_ends_with($class, 'Error')) {
                $out[] = self::NS . $class;
            }
        }

        return $out;
    }
}
