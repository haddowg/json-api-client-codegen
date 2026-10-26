<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The worked examples an operation's JSON:API media types carry, from contract 12.
 *
 * `$request` is the request body's example and `$responses` each success response's, by status.
 * Both are the documents exactly as the server would send or accept them, decoded. The projector
 * leaves an operation with no example rather than invent a value, so an operation can have a
 * request example and no response one, or the reverse, and most have none of either before
 * contract 12. The descriptor holds no `OperationExamples` at all when an operation has neither.
 *
 * Error responses are not here: the document shares one error response per status across every
 * operation, and an operation already lists its error statuses.
 */
final class OperationExamples
{
    /**
     * @param array<array-key, mixed>|null             $request
     * @param array<int, array<array-key, mixed>> $responses success status => example, ascending
     */
    public function __construct(
        public readonly ?array $request,
        public readonly array $responses,
    ) {}

    /**
     * The example a generated method's docblock shows: the request body when there is one, else
     * the first success response.
     *
     * @return array{label: string, example: array<array-key, mixed>}|null
     */
    public function primary(): ?array
    {
        if ($this->request !== null) {
            return ['label' => 'Request body', 'example' => $this->request];
        }
        foreach ($this->responses as $status => $example) {
            return ['label' => $status . ' response', 'example' => $example];
        }

        return null;
    }
}
