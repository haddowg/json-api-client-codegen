<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * A custom action, reached under `actions()` on the type accessor or the id-handle.
 *
 * The input and output kinds drive the generated signature: `none` takes no parameters, `raw`
 * takes a body plus `$contentType`, `document` takes the write forms for `$inputType`; the
 * output is `void`, a meta array, or the resource view of `$outputType`.
 */
final class ActionDescriptor
{
    /** @param list<int> $errorStatuses sorted ascending */
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
    ) {}
}
