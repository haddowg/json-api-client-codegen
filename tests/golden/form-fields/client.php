<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\FormFields;

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
 * The API's root client: one accessor per resource type, each built over the same transport, so one
 * client reaches the whole API.
 *
 * @generated from the OpenAPI document's resource types
 */
final class FormFieldsClient
{
    public readonly Articles $articles;
    public readonly Images $images;
    public readonly PeopleAccessor $people;
    public readonly Tags $tags;
    public readonly Videos $videos;

    private readonly Fetcher $fetcher;

    public function __construct(ClientOptions $options)
    {
        $this->fetcher = new Fetcher(new Transport($options->withErrorCodes(FormFieldsErrorCodes::MAP)));

        $this->articles = new Articles($this->fetcher);
        $this->images = new Images($this->fetcher);
        $this->people = new PeopleAccessor($this->fetcher);
        $this->tags = new Tags($this->fetcher);
        $this->videos = new Videos($this->fetcher);
    }

    /**
     * Apply a batch of write operations all-or-nothing, through the Atomic Operations extension.
     *
     * Record each write on the `Tx` the callback receives; nothing is sent until it returns, and
     * then everything is sent as one request. What the callback returns is returned here, so return
     * the handles whose results you want to read.
     *
     * Needs an access token granting the `batch` scope.
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
