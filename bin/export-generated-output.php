<?php

declare(strict_types=1);

/*
 * Writes the test suite's generated output into a directory, by default build/generated-output:
 * one directory per suite, generated from the documents package.json pins and the hand-built
 * ones under tests/fixtures, beside the music catalog's serialised descriptor. Nothing writes it
 * to the repository. `composer phpstan-generated` and `composer cs-check-generated` analyse it,
 * and CI exports the base's and the head's to diff a pull request's output for review.
 *
 * Usage:  php bin/export-generated-output.php [<directory>]
 */

use haddowg\JsonApiCodegen\Tests\Support\GeneratedOutput;

require \dirname(__DIR__) . '/vendor/autoload.php';

$directory = $argv[1] ?? GeneratedOutput::DIRECTORY;
GeneratedOutput::write($directory);

\fwrite(\STDOUT, "Wrote the generated output to {$directory}.\n");
