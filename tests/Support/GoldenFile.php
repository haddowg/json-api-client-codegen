<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

/**
 * Compares generated output against a committed expected file.
 *
 * Every emitter is proven this way, so the mechanism matters as much as what it checks: the
 * expected file is committed, a mismatch prints a diff, and regenerating is a deliberate act
 * (`UPDATE_GOLDEN=1 composer test`) whose result shows up as a reviewable change rather than a
 * green test.
 */
trait GoldenFile
{
    protected static function goldenPath(string $name): string
    {
        return \dirname(__DIR__) . '/golden/' . $name;
    }

    protected function assertMatchesGolden(string $name, string $actual): void
    {
        $path = self::goldenPath($name);

        if (\getenv('UPDATE_GOLDEN') === '1') {
            if (!\is_dir(\dirname($path))) {
                \mkdir(\dirname($path), 0o775, true);
            }
            \file_put_contents($path, $actual);
        }

        self::assertFileExists($path, \sprintf(
            'No golden file at %s. Generate it with `UPDATE_GOLDEN=1 composer test`, then review the result before committing it.',
            $path,
        ));

        $expected = \file_get_contents($path);
        self::assertIsString($expected);
        self::assertSame($expected, $actual, \sprintf(
            '%s no longer matches what the codegen produces. Read the diff: it is the change in what the codegen understood. Accept it with `UPDATE_GOLDEN=1 composer test` only once it is the change you meant.',
            $name,
        ));
    }
}
