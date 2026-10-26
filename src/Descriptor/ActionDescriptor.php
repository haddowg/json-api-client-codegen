<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * A custom action, reached under `actions()` on the type accessor or the id-handle.
 *
 * The input and output kinds drive the generated signature: `none` takes no parameters, `raw`
 * takes a body plus `$contentType`, `document` takes the write forms for `$inputType`; the
 * output is `void`, a meta array, or the resource view of `$outputType`.
 *
 * `$bodyRequired` is the request body's own `required` flag. OpenAPI defaults it to false, and an
 * action that leaves it so may be called with no body at all.
 *
 * From contract 10 an action can answer in media types other than JSON:API, listed in
 * `$responses`, and the caller picks one through `Accept`. `$requests` lists the media types a raw
 * body may be sent as when there is a choice to make: more than one, or a range such as
 * `image/*`, and from contract 11 also a lone form media type, whose body is a field array. `$summary` and `$description` are the operation's own prose, left null when they are
 * the projector's defaults. `$examples` holds the JSON:API request and response examples of a
 * contract-12 document, null when it has none; a raw media type keeps its own.
 *
 * From contract 15 an action answering to several methods has one operation, and one
 * descriptor, per method. The preferred method keeps the action's name, and each other one goes
 * under `$alias`. `$safety` is what the operation's `x-safety` declares, null when it declares
 * nothing.
 */
final class ActionDescriptor
{
    /**
     * @param list<int>                $errorStatuses sorted ascending
     * @param list<RawMediaDescriptor> $responses     in document order
     * @param list<RawMediaDescriptor> $requests      in document order
     */
    public function __construct(
        public readonly string $name,
        public readonly ActionScope $scope,
        public readonly string $path,
        public readonly string $method,
        public readonly ActionInput $input,
        public readonly ActionOutput $output,
        public readonly ?string $inputType,
        public readonly ?string $outputType,
        public readonly ?Cardinality $outputCardinality,
        public readonly ?string $contentType,
        public readonly array $errorStatuses,
        public readonly ?ProjectionDescriptor $projection,
        public readonly bool $bodyRequired = true,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly array $responses = [],
        public readonly array $requests = [],
        public readonly ?OperationExamples $examples = null,
        public readonly ?string $alias = null,
        public readonly ?ActionSafety $safety = null,
    ) {}

    /** The name the action is keyed and generated under: its alias when it has one, else its own. */
    public function memberName(): string
    {
        return $this->alias ?? $this->name;
    }

    /** Whether the action answers with JSON:API as well as, or instead of, a raw body. */
    public function answersJsonApi(): bool
    {
        return $this->output !== ActionOutput::None || $this->responses === [];
    }
}
