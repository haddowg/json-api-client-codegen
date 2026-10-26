<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\RawMediaDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RawMediaKind;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Emit\Surface\RawBodies;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 10 lets a custom action answer in media types other than JSON:API (core ADR
 * 0173), and an action can take its raw body in several. The witness declares the first; the
 * second is patched into a copy of it here, since no released server projects one yet.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(ActionSource::class)]
#[CoversClass(RawBodies::class)]
final class ContractTenTest extends TestCase
{
    private const array IMPORT = ['paths', '/articles/-actions/import', 'post', 'requestBody'];

    private const array DRAFT = ['paths', '/articles/{id}/-actions/draft', 'post', 'requestBody', 'content'];

    public function testEachRawResponseIsReadAsBinaryTextOrJson(): void
    {
        $report = self::action(self::json(), 'articles', 'report');

        self::assertSame(
            [['text/csv', RawMediaKind::Text, null], ['application/pdf', RawMediaKind::Binary, null], ['application/json', RawMediaKind::Json, 'ArticleSummary']],
            \array_map(static fn(RawMediaDescriptor $media): array => [$media->mediaType, $media->kind, $media->component], $report->responses),
        );
        self::assertTrue($report->answersJsonApi(), 'the JSON:API meta document is still the default');

        $cover = self::action(self::json(), 'articles', 'cover');
        self::assertCount(1, $cover->responses);
        self::assertSame(RawMediaKind::Binary, $cover->responses[0]->kind);
        self::assertFalse($cover->answersJsonApi(), 'the cover is only ever an image');
    }

    public function testTheProjectorsDefaultProseIsNotCarried(): void
    {
        $json = self::json();

        self::assertNull(self::action($json, 'articles', 'report')->summary);
        self::assertNull(self::action($json, 'articles', 'report')->description);
        self::assertSame('Publish the article', self::action($json, 'articles', 'publish')->summary);
        self::assertSame('Save a draft revision.', self::action($json, 'articles', 'draft')->description);
    }

    public function testAuthoredProseLeadsTheDocblock(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        self::assertStringContainsString('     * Publish the article: `POST /articles/{id}/-actions/publish`.', $source);
        self::assertStringContainsString("     * any of its three input forms.\n     *\n     * Save a draft revision.\n", $source);
    }

    public function testAnActionWithoutRawContentSerialisesWithoutTheNewMembers(): void
    {
        $actions = DescriptorSerializer::toArray(DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')));
        $stats = self::at($actions, ['resources', 'articles', 'actions', 'stats']);
        self::assertIsArray($stats);

        foreach (['summary', 'description', 'responses', 'requests'] as $member) {
            self::assertArrayNotHasKey($member, $stats);
        }
    }

    public function testSeveralRequestMediaTypesAreChosenByContentType(): void
    {
        $json = self::patched(self::IMPORT, [
            'content' => [
                'text/csv' => ['schema' => ['type' => 'string']],
                'application/json' => ['schema' => ['$ref' => '#/components/schemas/ArticleSummary']],
                'image/*' => ['schema' => ['type' => 'string', 'format' => 'binary']],
            ],
            'required' => true,
        ]);

        $import = self::action($json, 'articles', 'import');
        self::assertSame(
            [['text/csv', RawMediaKind::Text], ['application/json', RawMediaKind::Json], ['image/*', RawMediaKind::Binary]],
            \array_map(static fn(RawMediaDescriptor $media): array => [$media->mediaType, $media->kind], $import->requests),
        );

        $source = self::generate($json)['resource-articles.php'];
        self::assertStringContainsString("     * @template TContentType of 'text/csv'|'application/json'|non-empty-string\n", $source);
        self::assertStringContainsString('     * @param TContentType ', $source);
        self::assertStringContainsString("(TContentType is 'text/csv' ? string : (TContentType is 'application/json' ? array{total: int<0, max>, byStatus?: array<string, mixed>} : StreamInterface|string)) \$body", $source);
        self::assertStringContainsString("public function import(\n        string \$contentType,\n        StreamInterface|string|array \$body,\n    ): void {", $source);
        self::assertStringContainsString("if (!\\in_array(\$contentType, ['text/csv', 'application/json'], true) && !\\str_starts_with(\$contentType, 'image/')) {", $source);
        self::assertStringContainsString("\$contentType === 'application/json' => \\json_encode(\$body, \\JSON_THROW_ON_ERROR),", $source);
        self::assertStringContainsString("Request::post('/articles/-actions/import')->withRawBody(\$payload, \$contentType)", $source);
    }

    public function testOneMediaTypeRangeStillAsksForTheConcreteType(): void
    {
        $json = self::patched(self::IMPORT, [
            'content' => ['image/*' => ['schema' => ['type' => 'string', 'format' => 'binary']]],
            'required' => false,
        ]);

        $source = self::generate($json)['resource-articles.php'];
        self::assertStringNotContainsString('TContentType', $source);
        self::assertMatchesRegularExpression('/@param non-empty-string +\$contentType\n     \* @param StreamInterface\|string\|null \$body\n/', $source);
        self::assertStringContainsString("public function import(\n        string \$contentType,\n        StreamInterface|string|null \$body = null,\n    ): void {", $source);
        self::assertStringContainsString("\$body === null ? \$request : \$request->withRawBody(\$body, \$contentType)", $source);
        self::assertStringContainsString("The \"import\" action takes its body as image/*, not %s.", $source);
    }

    public function testOneConcreteRequestMediaTypeKeepsTheSingleBodySignature(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        self::assertStringContainsString('public function import(StreamInterface|string|null $body = null): void', $source);
    }

    public function testRawBodiesOfferedBesideJsonApiGenerateTheDocumentAndWarn(): void
    {
        $json = self::patched(self::DRAFT, [
            'application/vnd.api+json' => ['schema' => ['$ref' => '#/components/schemas/ArticlesCreateRequest']],
            'text/csv' => ['schema' => ['type' => 'string']],
        ]);

        $descriptor = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'));
        self::assertContains('The "draft" action at /articles/{id}/-actions/draft also accepts text/csv; only its JSON:API body is generated.', $descriptor->warnings);
        self::assertSame(self::generate(self::json())['resource-articles.php'], self::generate($json)['resource-articles.php']);
    }

    private static function action(string $json, string $type, string $name): ActionDescriptor
    {
        $action = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource($type)->actions[$name] ?? null;
        self::assertNotNull($action);

        return $action;
    }

    /**
     * @return array<string, string>
     */
    private static function generate(string $json): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness')),
            new GeneratorConfig(ContractWitnessTest::NAMESPACE, 'Witness', PhpTarget::Php84),
            'x',
        );
    }

    /**
     * The witness with the member at `$at` replaced.
     *
     * @param list<string> $at
     */
    private static function patched(array $at, mixed $value): string
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);

        return \json_encode(self::with($document, $at, $value), \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param list<string> $at
     */
    private static function at(mixed $node, array $at): mixed
    {
        foreach ($at as $key) {
            self::assertIsArray($node);
            self::assertArrayHasKey($key, $node);
            $node = $node[$key];
        }

        return $node;
    }

    /**
     * @param list<string> $at
     */
    private static function with(mixed $node, array $at, mixed $value): mixed
    {
        $key = \array_shift($at);
        if ($key === null) {
            return $value;
        }
        self::assertIsArray($node);
        self::assertArrayHasKey($key, $node);
        $node[$key] = self::with($node[$key], $at, $value);

        return $node;
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
