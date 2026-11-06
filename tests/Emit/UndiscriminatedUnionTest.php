<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Emit\ReadModel\Shape;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\Article;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\GoldenFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * A `oneOf` with no discriminator whose branches each require a member no other branch
 * declares reads as one class per branch, chosen by that member, and writes as a union of
 * array shapes. Core projects such a union from a `OneOf` field with no discriminator, naming
 * each branch by its `title`; this generates from a copy of the witness whose `articles` carries
 * one, a `shape` that is a circle or a rectangle, exactly as core projects it.
 *
 * `tests/golden/unions` is that copy's whole generated output on the 8.3 target, so its reads
 * run here on any supported PHP, and is analysed at level 9 by `composer phpstan-generated`
 * beside `tests/GeneratedTypes/UndiscriminatedUnionTypes.php`.
 */
#[CoversClass(Shape::class)]
#[CoversClass(ReadModelEmitter::class)]
final class UndiscriminatedUnionTest extends TestCase
{
    use GoldenFile;

    public const string NAMESPACE = 'haddowg\\JsonApiCodegen\\Tests\\Golden\\Unions';

    public function testEveryGeneratedFileMatchesItsGoldenFile(): void
    {
        foreach (self::generate() as $path => $contents) {
            $this->assertMatchesGolden('unions/' . $path, $contents);
        }
    }

    public function testTheGoldenDirectoryHoldsNothingTheCodegenNoLongerGenerates(): void
    {
        $golden = \array_map('basename', \glob(self::goldenPath('unions') . '/*.php') ?: []);
        \sort($golden, \SORT_STRING);
        $generated = \array_keys(self::generate());
        \sort($generated, \SORT_STRING);

        self::assertSame($generated, $golden, 'Delete golden files for output the codegen no longer generates.');
    }

    public function testEachBranchIsAClassNamedByItsTitleAndTheWriteIsAShapeUnion(): void
    {
        $articles = self::generate()['resource-articles.php'];

        self::assertStringContainsString('public function shape(): ArticleShapeCircle|ArticleShapeRectangle|null', $articles);
        self::assertStringContainsString('final class ArticleShapeCircle', $articles);
        self::assertStringContainsString('final class ArticleShapeRectangle', $articles);
        self::assertStringContainsString("\\array_key_exists('radius', \$raw) && !\\array_key_exists('width', \$raw) => ArticleShapeCircle::_from(\$raw),", $articles);
        self::assertStringContainsString('@param array{radius: int}|array{width: int, height: int}|Missing|null $shape', $articles);
        self::assertStringNotContainsString('...<string, never>', $articles, 'shapes stay plain: a union of sealed shapes collapses into one in PHPStan');
    }

    public function testTheMemberOnlyOneBranchCarriesSelectsItsClass(): void
    {
        $circle = self::shape(['radius' => 2]);
        self::assertIsObject($circle);
        self::assertSame(self::NAMESPACE . '\\ArticleShapeCircle', $circle::class);
        self::assertSame(2, self::member($circle, 'radius'));

        $rectangle = self::shape(['width' => 3, 'height' => 4]);
        self::assertIsObject($rectangle);
        self::assertSame(self::NAMESPACE . '\\ArticleShapeRectangle', $rectangle::class);
        self::assertSame(12, self::member($rectangle, 'width') * self::member($rectangle, 'height'));
    }

    public function testANullValueReadsAsNull(): void
    {
        self::assertNull(self::shape(null));
    }

    public function testAValueCarryingNoBranchOrTwoIsMalformed(): void
    {
        foreach ([['colour' => 'red'], ['radius' => 1, 'width' => 2, 'height' => 3]] as $value) {
            try {
                self::shape($value);
                self::fail('Expected MalformedDocument for ' . \json_encode($value, \JSON_THROW_ON_ERROR));
            } catch (MalformedDocument $e) {
                self::assertSame('The "shape" attribute of an "articles" resource matches none of its branches: expected exactly one of the members "radius", "width".', $e->getMessage());
            }
        }
    }

    public function testABranchMissingARequiredMemberIsMalformed(): void
    {
        $this->expectException(MalformedDocument::class);

        self::shape(['width' => 3]);
    }

    public function testAUnionWhoseBranchesCannotBeToldApartStaysMixed(): void
    {
        $json = \str_replace('"required":["width","height"]', '"required":[]', self::json());
        $articles = self::generate($json)['resource-articles.php'];

        self::assertStringContainsString('public function shape(): mixed', $articles);
    }

    /**
     * An article's `shape()`, from a response carrying `$value`.
     *
     * @param array<string, mixed>|null $value
     */
    private static function shape(?array $value): mixed
    {
        require_once \dirname(__DIR__) . '/golden/unions/autoload.php';

        $raw = ['type' => 'articles', 'id' => '1', 'attributes' => ['shape' => $value]];

        return Article::_hydrate($raw, ResourceContext::of(['data' => $raw]))->shape();
    }

    private static function member(object $branch, string $name): int
    {
        $value = (new \ReflectionProperty($branch, $name))->getValue($branch);
        self::assertIsInt($value);

        return $value;
    }

    /**
     * @return array<string, string>
     */
    private static function generate(?string $json = null): array
    {
        $json ??= self::json();

        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness with an undiscriminated union')),
            new GeneratorConfig(self::NAMESPACE, 'Unions', PhpTarget::Php83),
            \hash('sha256', $json),
        );
    }

    /** The witness with `articles` carrying a `shape` that is a circle or a rectangle, as core projects it. */
    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);
        $document = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        $shape = ['oneOf' => [
            ['type' => 'object', 'title' => 'circle', 'properties' => ['radius' => ['type' => 'integer']], 'required' => ['radius'], 'additionalProperties' => false],
            ['type' => 'object', 'title' => 'rectangle', 'properties' => ['width' => ['type' => 'integer'], 'height' => ['type' => 'integer']], 'required' => ['width', 'height'], 'additionalProperties' => false],
            ['type' => 'null'],
        ]];

        foreach (['ArticlesAttributes' => true, 'ArticlesCreateAttributes' => false, 'ArticlesUpdateAttributes' => false] as $name => $required) {
            $schema = &$document;
            foreach (['components', 'schemas', $name] as $key) {
                self::assertIsArray($schema);
                self::assertArrayHasKey($key, $schema);
                $schema = &$schema[$key];
            }
            self::assertIsArray($schema);
            self::assertIsArray($schema['properties'] ?? null);
            $schema['properties']['shape'] = $shape;
            if ($required) {
                self::assertIsArray($schema['required'] ?? null);
                $schema['required'][] = 'shape';
            }
            unset($schema);
        }

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }
}
