<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\OperationExamples;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\RelationVerb;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\ExampleTag;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 12 puts a worked example on each operation's JSON:API request body and
 * success responses (core ADR 0176). The witness carries them on most of its operations, a delete
 * whose `200` answers with meta among them, and a raw result keeps its own example on its media
 * type.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(OperationExamples::class)]
#[CoversClass(ExampleTag::class)]
final class ContractTwelveTest extends TestCase
{
    private const array VIDEO_CREATE = ['paths', '/videos', 'post', 'requestBody', 'content', 'application/vnd.api+json', 'example'];

    public function testTheWitnessCarriesExamplesAndIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(18, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testAnOperationCarriesItsRequestAndSuccessResponseExamples(): void
    {
        $create = self::resource('videos')->operation(OperationKind::Create);
        self::assertNotNull($create);
        self::assertNotNull($create->examples);

        self::assertSame(
            ['data' => ['type' => 'videos', 'attributes' => ['url' => 'https://cdn.example.com/intro.mp4', 'seconds' => 90]]],
            $create->examples->request,
        );
        self::assertSame([201], \array_keys($create->examples->responses));
        self::assertSame('videos', self::dig($create->examples->responses[201], 'data', 'type'));
    }

    public function testAResponseOnlyExampleLeavesTheRequestNull(): void
    {
        $delete = self::resource('articles')->operation(OperationKind::Delete);
        self::assertNotNull($delete?->examples);

        self::assertNull($delete->examples->request);
        self::assertSame([200 => ['meta' => ['deleted' => true]]], $delete->examples->responses);
        self::assertSame(['label' => '200 response', 'example' => ['meta' => ['deleted' => true]]], $delete->examples->primary());
    }

    public function testRelationEndpointsAndActionsCarryTheirExamplesToo(): void
    {
        $articles = self::resource('articles');

        $set = $articles->relation('author')?->mutation(RelationVerb::Set);
        self::assertSame(['data' => ['type' => 'people', 'id' => 'per-1']], $set?->examples?->request);

        $stats = $articles->actions['stats'] ?? null;
        self::assertNotNull($stats?->examples);
        self::assertNull($stats->examples->request);
        self::assertSame([200], \array_keys($stats->examples->responses));

        // A raw result's example sits on its own media type, never among the JSON:API ones.
        $report = $articles->actions['report'] ?? null;
        self::assertNotNull($report);
        self::assertNull($report->examples);
        $csv = \array_values(\array_filter($report->responses, static fn($media): bool => $media->mediaType === 'text/csv'));
        self::assertSame("id,title\n1,Hello\n", $csv[0]->example ?? null);
    }

    public function testAnOperationWithoutExamplesHasNone(): void
    {
        self::assertNull(self::resource('articles')->operation(OperationKind::Create)?->examples);
        self::assertNull(self::resource('people')->operation(OperationKind::FetchOne)?->examples);
    }

    public function testADocumentWithoutExamplesSerialisesAsItAlwaysDid(): void
    {
        $json = \file_get_contents(Fixtures::path('paginators.openapi.json'));
        self::assertIsString($json);
        $api = DescriptorBuilder::build(SpecDocument::fromJson($json, 'paginators'));

        self::assertStringNotContainsString('"examples"', DescriptorSerializer::toJson($api));
        self::assertStringNotContainsString('"example"', DescriptorSerializer::toJson($api));
        foreach (Generator::default()->generate($api, new GeneratorConfig('App\\Paginators', 'Paginators', PhpTarget::Php84), 'x') as $source) {
            self::assertStringNotContainsString('@example', $source);
        }
    }

    public function testTheSerialisedDescriptorKeysResponsesByStatus(): void
    {
        $serialised = DescriptorSerializer::toArray(self::api());
        $create = self::dig($serialised, 'resources', 'videos', 'operations', OperationKind::Create->value, 'examples');

        self::assertIsArray($create);
        self::assertSame(['request', 'responses'], \array_keys($create));
        self::assertEquals((object) [201 => self::resource('videos')->operation(OperationKind::Create)?->examples?->responses[201]], $create['responses']);
    }

    public function testTheDocblockQuotesTheRequestBodyElseTheFirstResponse(): void
    {
        $files = self::generate(self::json());

        self::assertStringContainsString(
            "     * @example Request body:\n"
            . "     *     {\n"
            . "     *         \"data\": {\n"
            . "     *             \"type\": \"videos\",\n"
            . "     *             \"attributes\": {\n"
            . "     *                 \"url\": \"https://cdn.example.com/intro.mp4\",\n"
            . "     *                 \"seconds\": 90\n"
            . "     *             }\n"
            . "     *         }\n"
            . "     *     }\n"
            . "     */\n"
            . '    public function create(',
            $files['resource-videos.php'],
        );
        self::assertStringContainsString(
            "     * @example 200 response:\n"
            . "     *     {\n"
            . "     *         \"meta\": {\n"
            . "     *             \"deleted\": true\n"
            . "     *         }\n"
            . "     *     }\n"
            . "     */\n"
            . '    public function delete(): void',
            $files['resource-articles.php'],
        );
    }

    public function testAStringCannotCloseTheDocblockAndTheExampleStaysJson(): void
    {
        $json = self::patched(self::VIDEO_CREATE, ['data' => ['type' => 'videos', 'attributes' => ['url' => 'https://x.example/*/a.mp4', 'seconds' => 1]]]);
        // The 8.3 target, so the running PHP can parse it whatever its version.
        $source = self::generate($json, PhpTarget::Php83)['resource-videos.php'];

        self::assertStringContainsString('"url": "https://x.example/*\/a.mp4",', $source);
        self::assertStringNotContainsString('/*/', $source);

        // The source still parses, and the quoted example reads back as the document's value.
        $tokens = \token_get_all($source, \TOKEN_PARSE);
        $create = \array_values(\array_filter(
            $tokens,
            static fn($token): bool => \is_array($token) && $token[0] === \T_DOC_COMMENT && \str_contains($token[1], 'x.example'),
        ));
        self::assertNotSame([], $create);
        \assert(\is_array($create[0]));
        $body = \substr($create[0][1], (int) \strpos($create[0][1], '@example Request body:') + \strlen('@example Request body:'));
        $body = (string) \preg_replace('/^\s*\*( {4})?/m', '', \substr($body, 0, (int) \strrpos($body, '*/')));
        self::assertSame('https://x.example/*/a.mp4', self::dig(\json_decode($body, true, flags: \JSON_THROW_ON_ERROR), 'data', 'attributes', 'url'));
    }

    public function testAnExampleOverTheLineLimitStaysInTheDescriptorButLeavesTheDocblock(): void
    {
        $attributes = ['url' => 'https://cdn.example.com/intro.mp4', 'seconds' => 90];
        for ($i = 0; $i < ExampleTag::MAX_LINES; ++$i) {
            $attributes['extra' . $i] = $i;
        }
        $json = self::patched(self::VIDEO_CREATE, ['data' => ['type' => 'videos', 'attributes' => $attributes]]);

        $create = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource('videos')?->operation(OperationKind::Create);
        self::assertSame($attributes, self::dig($create?->examples?->request, 'data', 'attributes'));

        $source = self::generate($json)['resource-videos.php'];
        self::assertStringNotContainsString('"extra0"', $source);
        self::assertStringContainsString("when the response carries no primary resource\n     */\n    public function create(", $source);
    }

    public function testAJsonApiExampleThatIsNotADocumentIsAnError(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('paths./videos.post.requestBody.content.application/vnd.api+json.example to be a JSON:API document');

        DescriptorBuilder::build(SpecDocument::fromJson(self::patched(self::VIDEO_CREATE, 'not a document'), 'the witness'));
    }

    private static function api(): ApiDescriptor
    {
        return DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness'));
    }

    private static function resource(string $type): ResourceDescriptor
    {
        $resource = self::api()->resource($type);
        self::assertNotNull($resource);

        return $resource;
    }

    private static function dig(mixed $value, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            self::assertIsArray($value);
            self::assertArrayHasKey($key, $value);
            $value = $value[$key];
        }

        return $value;
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
            self::assertArrayHasKey($key, $node);
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
