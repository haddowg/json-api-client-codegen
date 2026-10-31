<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Atomic\Tx;
use haddowg\JsonApiClient\ClientOptions;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\NotFound;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\TransportException;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;
use haddowg\JsonApiClient\Http\Transport;
use haddowg\JsonApiClient\Resources\Fetcher;

/**
 * The Music Catalog API, one accessor per resource type.
 *
 * Trimmed to `albums` here. A full run carries one property per type in the document, each
 * built over the same transport, so one client reaches the whole API.
 *
 * `atomic()` is here because `POST /operations` advertises the atomic extension. On a server that
 * does not, it is simply not generated.
 *
 * @generated from the Music Catalog API OpenAPI 3.1 document
 */
final class MusicCatalogClient
{
    public readonly Albums $albums;

    private readonly Fetcher $fetcher;

    public function __construct(ClientOptions $options)
    {
        $this->fetcher = new Fetcher(new Transport($options));

        $this->albums = new Albums($this->fetcher);
    }

    /**
     * Apply a batch of atomic operations.
     *
     * Applies an ordered batch of write operations all-or-nothing within a single transaction (the
     * JSON:API Atomic Operations extension).
     *
     * Record each write on the `Tx` the callback receives; nothing is sent until it returns, and
     * then everything is sent as one request. What the callback returns is returned here, so
     * return the handles whose results you want to read.
     *
     * @template R
     *
     * @param callable(Tx): R $batch
     *
     * @return R
     *
     * @throws BadRequest           when the operations document is malformed
     * @throws Unauthorized         when credentials are missing or invalid
     * @throws Forbidden            when an operation is not permitted
     * @throws NotFound             when an operation targets a resource that does not exist
     * @throws NotAcceptable        when the negotiated media type cannot be satisfied
     * @throws Conflict             when an operation conflicts with the resource state
     * @throws UnsupportedMediaType when the `Content-Type` is refused
     * @throws ValidationFailed     when an operation fails validation
     * @throws ServerError          when the server fails
     * @throws TransportException   when no response is received at all
     * @throws MalformedDocument    when the response does not carry one result per operation
     */
    public function atomic(callable $batch): mixed
    {
        return $this->fetcher->atomic('/operations', $batch);
    }
}
