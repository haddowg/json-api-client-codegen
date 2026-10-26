<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Enums\EnumEmitter;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Root\ClientEmitter;
use haddowg\JsonApiCodegen\Emit\Surface\SurfaceEmitter;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\GoldenFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Core's contract witness projects every branch its projector can reach in one document, so it
 * carries shapes no example server does: an integer-backed enum, types whose singular is the type
 * itself, a type the document describes without giving it a single endpoint, and from contract 10
 * actions answering in raw media types.
 *
 * `tests/golden/witness` is its whole generated output, analysed at level 9 by
 * `composer phpstan-generated` beside `tests/GeneratedTypes/RawActionTypes.php`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(EnumEmitter::class)]
#[CoversClass(SurfaceEmitter::class)]
#[CoversClass(ClientEmitter::class)]
final class ContractWitnessTest extends TestCase
{
    use GoldenFile;

    public const string NAMESPACE = 'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness';

    public function testEveryGeneratedFileMatchesItsGoldenFile(): void
    {
        foreach (self::generate() as $path => $contents) {
            $this->assertMatchesGolden('witness/' . $path, $contents);
        }
    }

    public function testTheGoldenDirectoryHoldsNothingTheCodegenNoLongerGenerates(): void
    {
        $golden = \array_map('basename', \glob(self::goldenPath('witness') . '/*.php') ?: []);
        \sort($golden, \SORT_STRING);
        $generated = \array_keys(self::generate());
        \sort($generated, \SORT_STRING);

        self::assertSame($generated, $golden, 'Delete golden files for output the codegen no longer generates.');
    }

    public function testAnIntegerEnumIsAnIntBackedEnum(): void
    {
        $files = self::generate();

        self::assertStringContainsString("enum Priority: int\n{\n    case Low = 1;\n\n    case High = 2;\n}\n", $files['enums.php']);
        self::assertStringContainsString('@phpstan-type ArticlePriorityInput Priority|1|2', $files['resource-articles.php']);
        self::assertStringContainsString("return \$this->_nullableEnum('priority', Priority::class);", $files['resource-articles.php']);
    }

    public function testAnEnumMixingIntegersAndStringsIsRefusedByPointer(): void
    {
        $mixed = \str_replace(
            "\"Priority\": {\n                \"enum\": [\n                    1,\n                    2\n",
            "\"Priority\": {\n                \"enum\": [\n                    1,\n                    \"high\"\n",
            self::json(),
            $replaced,
        );
        self::assertSame(1, $replaced);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('components.schemas.Priority.enum.1');

        DescriptorBuilder::build(SpecDocument::fromJson($mixed, 'the witness'));
    }

    public function testATypeThatIsItsOwnSingularKeepsItsResourceClassName(): void
    {
        $files = self::generate();

        self::assertStringContainsString('final class People extends PeopleBase', $files['resource-people.php']);
        self::assertStringContainsString('final class PeopleAccessor', $files['resource-people.php']);
        self::assertStringContainsString('public readonly PeopleAccessor $people;', $files['client.php']);
    }

    public function testATypeWithNoEndpointGetsNoAccessor(): void
    {
        $files = self::generate();

        self::assertStringContainsString('final class Healthcheck extends HealthcheckBase', $files['resource-healthcheck.php']);
        self::assertStringNotContainsString('HealthcheckAccessor', $files['resource-healthcheck.php']);
        self::assertStringNotContainsString('healthcheck', $files['client.php']);
    }

    /**
     * @return array<string, string>
     */
    private static function generate(): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')),
            new GeneratorConfig(self::NAMESPACE, 'Witness', PhpTarget::Php84),
            \hash_file('sha256', Fixtures::path('contract-witness.openapi.json')) ?: '',
        );
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
