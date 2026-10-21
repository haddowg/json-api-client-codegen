<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Emit\Assembler;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use haddowg\JsonApiCodegen\Tests\Support\GoldenFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The whole generated output for the music-catalog fixture, pinned file by file.
 *
 * `tests/golden/generated` is also what `composer phpstan-generated` analyses at level 9, so a
 * golden file is both the reviewable diff of a change and the proof that its output type-checks.
 */
#[CoversClass(Generator::class)]
#[CoversClass(Assembler::class)]
final class GeneratedOutputTest extends TestCase
{
    use GoldenFile;

    public function testEveryGeneratedFileMatchesItsGoldenFile(): void
    {
        foreach (Generated::musicCatalog() as $path => $contents) {
            $this->assertMatchesGolden('generated/' . $path, $contents);
        }
    }

    public function testTheGoldenDirectoryHoldsNothingTheCodegenNoLongerGenerates(): void
    {
        $golden = \array_map('basename', \glob(self::goldenPath('generated') . '/*.php') ?: []);
        \sort($golden, \SORT_STRING);
        $generated = \array_keys(Generated::musicCatalog());
        \sort($generated, \SORT_STRING);

        self::assertSame($generated, $golden, 'Delete golden files for output the codegen no longer generates.');
    }

    public function testGenerationIsStable(): void
    {
        $first = Generator::default()->generate(Fixtures::musicCatalogDescriptor(), Generated::config(), 'x');
        $again = Generator::default()->generate(Fixtures::musicCatalogDescriptor(), Generated::config(), 'x');

        self::assertSame($first, $again);
    }
}
