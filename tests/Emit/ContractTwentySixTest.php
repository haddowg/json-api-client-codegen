<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ActionOutput;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 26 lets an action answer `201 Created` with the resource it created. The
 * witness's `duplicate` declares only that document and `restore` declares a `200` document
 * beside it, both naming `articles`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(ActionDescriptor::class)]
#[CoversClass(ContractCompatibility::class)]
final class ContractTwentySixTest extends TestCase
{
    public function testTheWitnessIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(26, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testACreatedDocumentIsTheActionResult(): void
    {
        $api = DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness'));
        $articles = $api->resource('articles');
        self::assertNotNull($articles);
        $actions = $articles->actions;

        foreach (['duplicate', 'restore'] as $name) {
            self::assertSame(ActionOutput::Document, $actions[$name]->output, $name);
            self::assertSame('articles', $actions[$name]->outputType, $name);
            self::assertSame(Cardinality::One, $actions[$name]->outputCardinality, $name);
        }
        self::assertSame([], \array_values(\array_filter($api->warnings, static fn(string $warning): bool => \str_contains($warning, 'answers with both'))));
    }

    public function testTheMethodsReadTheCreatedResource(): void
    {
        $source = Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')),
            new GeneratorConfig(ContractWitnessTest::NAMESPACE, 'Witness', PhpTarget::Php84),
            'x',
        )['resource-articles.php'];

        self::assertStringContainsString('public function duplicate(callable|ArticleProjection|null $projection = null): ArticleBase', $source);
        self::assertStringContainsString('public function restore(callable|ArticleProjection|null $projection = null): ArticleBase', $source);
    }

    public function testA200And201NamingDifferentTypesWarnsAndTypesByThe200(): void
    {
        $json = self::patched(
            ['paths', '/articles/{id}/-actions/restore', 'post', 'responses', '201', 'content', 'application/vnd.api+json', 'schema'],
            ['$ref' => '#/components/schemas/PeopleDocument'],
        );

        $api = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'));

        self::assertSame('articles', $api->resource('articles')?->actions['restore']->outputType);
        self::assertContains('The "restore" action at /articles/{id}/-actions/restore answers with both a "articles" and a "people" document; its result is typed as "articles".', $api->warnings);
    }

    /**
     * The witness with the value at `$keys` replaced.
     *
     * @param list<string> $keys
     */
    private static function patched(array $keys, mixed $value): string
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        $node = &$document;
        foreach ($keys as $key) {
            self::assertIsArray($node);
            $node = &$node[$key];
        }
        $node = $value;
        unset($node);

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::witnessPath());
        self::assertIsString($json);

        return $json;
    }
}
