<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\Paginators;

use haddowg\JsonApiClient\ClientOptions;
use haddowg\JsonApiClient\Http\Transport;
use haddowg\JsonApiClient\Resources\Fetcher;

/**
 * The API's root client: one accessor per resource type, each built over the same transport, so one
 * client reaches the whole API.
 *
 * @generated from the OpenAPI document's resource types
 */
final class PaginatorsClient
{
    public readonly Chapters $chapters;
    public readonly Comments $comments;
    public readonly Events $events;
    public readonly Logs $logs;
    public readonly Posts $posts;
    public readonly Tags $tags;
    public readonly Writers $writers;

    public function __construct(ClientOptions $options)
    {
        $fetcher = new Fetcher(new Transport($options));

        $this->chapters = new Chapters($fetcher);
        $this->comments = new Comments($fetcher);
        $this->events = new Events($fetcher);
        $this->logs = new Logs($fetcher);
        $this->posts = new Posts($fetcher);
        $this->tags = new Tags($fetcher);
        $this->writers = new Writers($fetcher);
    }
}
