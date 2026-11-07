<?php

declare(strict_types=1);

/*
 * Adds the `unions` suite, the witness's generated output on the 8.3 target, to the main
 * analysis, so that output is held to the codegen's own PHP floor and the tests running it
 * type-check. Nothing commits it, so loading this config writes the generated output first,
 * from the documents package.json pins.
 */

use haddowg\JsonApiCodegen\Tests\Support\GeneratedOutput;

require_once \dirname(__DIR__) . '/vendor/autoload.php';

GeneratedOutput::write(GeneratedOutput::DIRECTORY);

return ['parameters' => ['paths' => [GeneratedOutput::DIRECTORY . '/unions']]];
