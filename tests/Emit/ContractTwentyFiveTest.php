<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Emit\Surface\Throws;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 25 lets a collection action take its type's `filter[…]` parameters. The
 * witness's `count` takes every filter of the article collection, and `bulk-archive` takes
 * `status`, `rating` and `labels`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(ActionDescriptor::class)]
#[CoversClass(ActionSource::class)]
#[CoversClass(Throws::class)]
final class ContractTwentyFiveTest extends TestCase
{
    public function testAnActionRecordsTheFiltersItTakesInDocumentOrder(): void
    {
        $articles = self::resource();

        self::assertSame(\array_keys($articles->filterable), \array_keys($articles->actions['count']->filterable));
        self::assertSame(['status', 'rating', 'labels'], \array_keys($articles->actions['bulk-archive']->filterable));
        self::assertEquals($articles->filterable['rating'], $articles->actions['bulk-archive']->filterable['rating']);
        self::assertSame([], $articles->actions['stats']->filterable);
    }

    public function testTheDescriptorSerialisesAnActionsFiltersOnlyWherePresent(): void
    {
        $serialised = DescriptorSerializer::toArray(DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')));
        $resources = $serialised['resources'];
        self::assertIsArray($resources);
        self::assertIsArray($resources['articles']);
        $actions = $resources['articles']['actions'];
        self::assertIsArray($actions);
        self::assertIsArray($actions['bulk-archive']);
        self::assertIsArray($actions['bulk-archive']['filterable']);
        self::assertSame(['status', 'rating', 'labels'], \array_keys($actions['bulk-archive']['filterable']));
        self::assertIsArray($actions['stats']);
        self::assertArrayNotHasKey('filterable', $actions['stats']);
    }

    public function testAFilteredActionTakesATypedFilterCheckedAndSentAsAReadSendsIt(): void
    {
        $actions = self::collectionActions(PhpTarget::Php84);

        self::assertStringContainsString('@param array{status?: string, rating?: array{min?: float, max?: float}, labels?: list<string>} $filter', $actions);
        self::assertStringContainsString('@throws UnknownFilterException when a filter name is not one the action takes', $actions);
        self::assertStringContainsString(
            "    public function bulkArchive(array \$filter = []): void\n"
            . "    {\n"
            . "        \$filter = FilterGuard::check(\$filter, ['status', 'rating', 'labels'], ['rating' => ['min', 'max']]);\n"
            . "\n"
            . "        \$this->fetcher->send(Request::post((new ReadQuery(filter: \$filter))->appendTo('/articles/-actions/bulk-archive')));\n"
            . "    }\n",
            $actions,
        );
    }

    public function testADateBoundNamesTheTypesDateInputAndIsImported(): void
    {
        $source = self::generate(PhpTarget::Php84)['resource-articles.php'];
        $actions = self::between($source, 'final class ArticleCollectionActions', "\n}\n");

        self::assertStringContainsString('publishedAt?: array{min?: ArticleDateInput, max?: ArticleDateInput}', $actions);
        self::assertStringContainsString('public function count(array $filter = []): array', $actions);
        $docblock = \substr($source, 0, (int) \strpos($source, 'final class ArticleCollectionActions'));
        self::assertStringContainsString('@phpstan-import-type ArticleDateInput from Article', \substr($docblock, (int) \strrpos($docblock, '/**')));
    }

    public function testAnActionWithoutFiltersKeepsItsSignature(): void
    {
        $actions = self::collectionActions(PhpTarget::Php84);

        self::assertStringContainsString('public function archive(): void', $actions);
        self::assertStringNotContainsString('FilterGuard::check($filter, [\'', self::between($actions, 'public function archive()', '    }'));
    }

    public function testTheEightPointThreeTargetGeneratesTheSameMethods(): void
    {
        self::assertStringContainsString('public function bulkArchive(array $filter = []): void', self::collectionActions(PhpTarget::Php83));
    }

    private static function collectionActions(PhpTarget $target): string
    {
        return self::between(self::generate($target)['resource-articles.php'], 'final class ArticleCollectionActions', "\n}\n");
    }

    private static function between(string $source, string $start, string $end): string
    {
        $from = \strpos($source, $start);
        self::assertIsInt($from);
        $to = \strpos($source, $end, $from);
        self::assertIsInt($to);

        return \substr($source, $from, $to - $from);
    }

    private static function resource(): ResourceDescriptor
    {
        $resource = DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness'))->resource('articles');
        self::assertNotNull($resource);

        return $resource;
    }

    /**
     * @return array<string, string>
     */
    private static function generate(PhpTarget $target): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')),
            new GeneratorConfig(ContractWitnessTest::NAMESPACE, 'Witness', $target),
            'x',
        );
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::witnessPath());
        self::assertIsString($json);

        return $json;
    }
}
