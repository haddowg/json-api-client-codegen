<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ActionOutput;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 17 lets an action that declares `SeeOther` with a media type answer a
 * JSON:API request with a `200` location document beside its `303` (core ADR 0185). The witness's
 * `download` answers only with a `text/csv` URL, `revision` with either its article or an
 * `application/octet-stream` URL, and `archive` declares a bare `SeeOther`, which documents only
 * the `303`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(ActionDescriptor::class)]
#[CoversClass(ActionSource::class)]
final class ContractSeventeenTest extends TestCase
{
    private const array LOCATION = [
        'allOf' => [
            ['$ref' => '#/components/schemas/LocationDocument'],
            ['type' => 'object', 'properties' => ['links' => ['type' => 'object', 'properties' => ['location' => ['type' => 'object', 'properties' => ['type' => ['type' => 'string', 'const' => 'application/pdf']]]]]]],
        ],
    ];

    public function testTheWitnessIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(17, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testAnActionAnsweringOnlyWithAUrlCarriesItsMediaType(): void
    {
        $download = self::resource(self::json())->actions['download'];

        self::assertSame('text/csv', $download->locationType);
        self::assertSame(ActionOutput::None, $download->output);
        self::assertNull($download->outputType);
        self::assertTrue($download->answersJsonApi());
    }

    public function testAnActionAnsweringWithADocumentOrAUrlCarriesBoth(): void
    {
        $revision = self::resource(self::json())->actions['revision'];

        self::assertSame('application/octet-stream', $revision->locationType);
        self::assertSame(ActionOutput::Document, $revision->output);
        self::assertSame('articles', $revision->outputType);
        self::assertSame(Cardinality::One, $revision->outputCardinality);
    }

    public function testABareSeeOtherIsNoLocation(): void
    {
        $actions = self::resource(self::json())->actions;

        self::assertNull($actions['archive']->locationType);
        self::assertSame(ActionOutput::None, $actions['archive']->output);
        self::assertNull($actions['publish']->locationType);
    }

    public function testTheDescriptorSerialisesTheLocationTypeOnlyWherePresent(): void
    {
        $serialised = DescriptorSerializer::toArray(DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')));
        $resources = $serialised['resources'];
        self::assertIsArray($resources);
        self::assertIsArray($resources['articles']);
        $actions = $resources['articles']['actions'];
        self::assertIsArray($actions);
        self::assertIsArray($actions['download']);
        self::assertSame('text/csv', $actions['download']['locationType']);
        self::assertIsArray($actions['archive']);
        self::assertArrayNotHasKey('locationType', $actions['archive']);
    }

    public function testAnOlderDocumentCarriesNoLocationType(): void
    {
        $json = \json_encode(DescriptorSerializer::toArray(Fixtures::musicCatalogDescriptor()), \JSON_THROW_ON_ERROR);

        self::assertStringNotContainsString('"locationType"', $json);
    }

    public function testALocationDocumentWithoutItsTypeConstIsMalformed(): void
    {
        $json = self::patched(
            ['paths', '/articles/{id}/-actions/download', 'get', 'responses', '200', 'content', 'application/vnd.api+json', 'schema'],
            ['$ref' => '#/components/schemas/LocationDocument'],
        );

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('links.location.type');

        DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'));
    }

    public function testTheDownloadMethodReturnsItsLocationAndFollowsNothing(): void
    {
        $actions = self::between(self::generate(self::json())['resource-articles.php'], 'final class ArticleActions', "\n}\n");

        self::assertStringContainsString(
            "    public function download(): ActionLocation\n"
            . "    {\n"
            . "        return \$this->fetcher->location(Request::get('/articles/' . \$this->id . '/-actions/download'));\n"
            . "    }\n",
            $actions,
        );
        self::assertStringContainsString('`text/csv`, as an `ActionLocation`. The URL is not followed.', \str_replace("\n     * ", ' ', $actions));
    }

    public function testTheRevisionMethodReturnsTheUnionToldApartAtRuntime(): void
    {
        $actions = self::between(self::generate(self::json())['resource-articles.php'], 'final class ArticleActions', "\n}\n");

        self::assertStringContainsString('@return ($projection is null ? ArticleBase|ActionLocation : (ArticleBase&TProj)|ActionLocation)', $actions);
        self::assertStringContainsString('public function revision(callable|ArticleProjection|null $projection = null): ArticleBase|ActionLocation', $actions);
        self::assertStringContainsString(
            "        \$context = \$this->fetcher->readOrLocation(\n"
            . "            Request::get(\$query->appendTo('/articles/' . \$this->id . '/-actions/revision')),\n"
            . "            \$query,\n"
            . "        );\n"
            . "\n"
            . "        if (\$context instanceof ActionLocation) {\n"
            . "            return \$context;\n"
            . "        }\n",
            $actions,
        );
    }

    public function testABareSeeOtherActionGeneratesAsBefore(): void
    {
        $actions = self::between(self::generate(self::json())['resource-articles.php'], 'final class ArticleCollectionActions', "\n}\n");

        self::assertStringContainsString(
            "    public function archive(): void\n"
            . "    {\n"
            . "        \$this->fetcher->send(Request::get('/articles/-actions/archive'));\n",
            $actions,
        );
    }

    public function testAMetaResultOrAUrlReadsTheMetaWhenThereIsNoLocation(): void
    {
        $json = self::patched(
            ['paths', '/articles/-actions/stats', 'get', 'responses', '200', 'content', 'application/vnd.api+json', 'schema'],
            ['oneOf' => [['$ref' => '#/components/schemas/MetaDocument'], self::LOCATION]],
        );
        self::assertSame('application/pdf', self::resource($json)->actions['stats']->locationType);
        self::assertSame(ActionOutput::Meta, self::resource($json)->actions['stats']->output);

        $actions = self::between(self::generate($json)['resource-articles.php'], 'final class ArticleCollectionActions', "\n}\n");

        self::assertStringContainsString('@return array<string, mixed>|ActionLocation', $actions);
        self::assertStringContainsString('public function stats(): array|ActionLocation', $actions);
        self::assertStringContainsString('return $context instanceof ActionLocation ? $context : $context->document()->meta();', $actions);
    }

    public function testACollectionResultOrAUrlReturnsEither(): void
    {
        $json = self::patched(
            ['paths', '/articles/-actions/stats', 'get', 'responses', '200', 'content', 'application/vnd.api+json', 'schema'],
            ['oneOf' => [['$ref' => '#/components/schemas/ArticlesCollection'], self::LOCATION]],
        );
        $stats = self::resource($json)->actions['stats'];
        self::assertSame(Cardinality::Many, $stats->outputCardinality);

        $actions = self::between(self::generate($json)['resource-articles.php'], 'final class ArticleCollectionActions', "\n}\n");

        self::assertStringContainsString('@return ResourceCollection<Article>|ActionLocation', $actions);
        self::assertStringContainsString('public function stats(): ResourceCollection|ActionLocation', $actions);
    }

    public function testTheEightPointThreeTargetGeneratesTheSameMethods(): void
    {
        $source = self::generate(self::json(), PhpTarget::Php83)['resource-articles.php'];

        self::assertStringContainsString('public function download(): ActionLocation', $source);
        self::assertStringContainsString('ArticleBase|ActionLocation', $source);
    }

    private static function between(string $source, string $start, string $end): string
    {
        $from = \strpos($source, $start);
        self::assertIsInt($from);
        $to = \strpos($source, $end, $from);
        self::assertIsInt($to);

        return \substr($source, $from, $to - $from);
    }

    private static function resource(string $json): ResourceDescriptor
    {
        $resource = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource('articles');
        self::assertNotNull($resource);

        return $resource;
    }

    /**
     * @return array<string, string>
     */
    private static function generate(string $json, PhpTarget $target = PhpTarget::Php84): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness')),
            new GeneratorConfig(ContractWitnessTest::NAMESPACE, 'Witness', $target),
            'x',
        );
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
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
