<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\FormFields;

use haddowg\JsonApiClient\ClientOptions;
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

    public function __construct(ClientOptions $options)
    {
        $fetcher = new Fetcher(new Transport($options->withErrorCodes(FormFieldsErrorCodes::MAP)));

        $this->articles = new Articles($fetcher);
        $this->images = new Images($fetcher);
        $this->people = new PeopleAccessor($fetcher);
        $this->tags = new Tags($fetcher);
        $this->videos = new Videos($fetcher);
    }
}
