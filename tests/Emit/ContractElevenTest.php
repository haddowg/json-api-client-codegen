<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\RawMediaDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RawMediaKind;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 11 lets a custom action accept several request bodies. The witness's
 * `bulk-import` takes CSV, any image, a multipart form or JSON; a form is a field array the
 * runtime encodes, since only the encoder knows the multipart boundary.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(RawMediaKind::class)]
#[CoversClass(ActionSource::class)]
final class ContractElevenTest extends TestCase
{
    private const array BULK_IMPORT = ['paths', '/articles/-actions/bulk-import', 'post', 'requestBody'];

    public function testTheWitnessIsAContractElevenDocumentThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(11, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testAMultipartBodyIsAFormWhateverItsSchemaSays(): void
    {
        $bulk = self::action(self::json(), 'bulk-import');

        self::assertSame(
            [['text/csv', RawMediaKind::Text], ['image/*', RawMediaKind::Binary], ['multipart/form-data', RawMediaKind::Multipart], ['application/json', RawMediaKind::Json]],
            \array_map(static fn(RawMediaDescriptor $media): array => [$media->mediaType, $media->kind], $bulk->requests),
        );
    }

    public function testAFormAmongSeveralGoesThroughTheRuntimesCheckedEncoder(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        self::assertStringContainsString("(TContentType is 'multipart/form-data' ? array<string, string|int|float|bool|FilePart> :", $source);
        self::assertStringContainsString("\$contentType === 'multipart/form-data' && \\is_array(\$body) => \$request->withMultipartBody(FormEncoding::multipartFields(\$body)),", $source);
        self::assertStringContainsString("\$contentType === 'application/json' => \$request->withRawBody(\\json_encode(\$body, \\JSON_THROW_ON_ERROR), \$contentType),", $source);
        self::assertStringContainsString("\$body instanceof StreamInterface, \\is_string(\$body) => \$request->withRawBody(\$body, \$contentType),", $source);
    }

    public function testAUrlencodedFormAmongSeveralIsEncodedAsAForm(): void
    {
        $json = self::patched([
            'content' => [
                'application/x-www-form-urlencoded' => ['schema' => ['type' => 'object']],
                'text/csv' => ['schema' => ['type' => 'string']],
            ],
            'required' => true,
        ]);

        $source = self::generate($json)['resource-articles.php'];
        self::assertStringContainsString("(TContentType is 'application/x-www-form-urlencoded' ? array<string, string|int|float|bool> : string) \$body", $source);
        self::assertStringContainsString("\$contentType === 'application/x-www-form-urlencoded' && \\is_array(\$body) => \$request->withFormBody(FormEncoding::formFields(\$body)),", $source);
        self::assertStringNotContainsString('$body === null => $request,', $source);
    }

    public function testALoneFormTakesItsFieldsWithoutAContentType(): void
    {
        $json = self::patched([
            'content' => ['multipart/form-data' => ['schema' => ['type' => 'object']]],
            'required' => true,
        ]);

        self::assertSame(RawMediaKind::Multipart, self::action($json, 'bulk-import')->requests[0]->kind);

        $source = self::generate($json)['resource-articles.php'];
        self::assertStringContainsString('@param array<string, string|int|float|bool|FilePart> $body', $source);
        self::assertStringContainsString('public function bulkImport(array $body): void', $source);
        self::assertStringContainsString("\$request = \$request->withMultipartBody(\$body);\n", $source);
    }

    public function testALoneOptionalFormMayBeLeftOut(): void
    {
        $json = self::patched([
            'content' => ['application/x-www-form-urlencoded' => ['schema' => ['type' => 'object']]],
            'required' => false,
        ]);

        $source = self::generate($json)['resource-articles.php'];
        self::assertStringContainsString('public function bulkImport(?array $body = null): void', $source);
        self::assertStringContainsString("\$request = \$body === null ? \$request : \$request->withFormBody(\$body);\n", $source);
        self::assertStringContainsString("        \$this->fetcher->send(\$request);\n", $source);
    }

    public function testAFormSchemaWithPropertiesNarrowsTheFields(): void
    {
        $json = self::patched([
            'content' => ['multipart/form-data' => ['schema' => [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string'],
                    'cover' => ['type' => 'string', 'format' => 'binary'],
                    'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'attachments' => ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'binary']],
                    'rows' => ['type' => 'integer'],
                    'weight' => ['type' => 'number'],
                    'draft' => ['type' => 'boolean'],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published']],
                    'meta' => ['type' => 'object'],
                    'content-language' => ['type' => 'string'],
                ],
                'required' => ['title'],
            ]]],
            'required' => true,
        ]);

        $media = self::action($json, 'bulk-import')->requests[0];
        self::assertNotNull($media->schema);
        self::assertFalse($media->additional);

        $source = self::generate($json)['resource-articles.php'];
        self::assertStringContainsString(
            "@param array{title: string, cover?: FilePart, tags?: list<string>, attachments?: list<FilePart>, rows?: int, weight?: int|float, draft?: bool, status?: 'draft'|'published', meta?: string|int|float|bool|FilePart, 'content-language'?: string} \$body",
            $source,
        );
        self::assertStringContainsString('public function bulkImport(array $body): void', $source);
    }

    public function testAdditionalPropertiesTypeTheOtherFields(): void
    {
        $fields = static fn(mixed $additional): string => self::generate(self::patched([
            'content' => ['multipart/form-data' => ['schema' => [
                'type' => 'object',
                'properties' => ['title' => ['type' => 'string']],
                'required' => ['title'],
                ...($additional === null ? [] : ['additionalProperties' => $additional]),
            ]]],
            'required' => true,
        ]))['resource-articles.php'];

        self::assertStringContainsString('@param array{title: string} $body', $fields(null));
        self::assertStringContainsString('@param array{title: string} $body', $fields(false));
        self::assertStringContainsString('@param array{title: string, ...<string, string|int|float|bool|FilePart|list<string|int|float|bool|FilePart>>} $body', $fields(true));
        self::assertStringContainsString('@param array{title: string, ...<string, int>} $body', $fields(['type' => 'integer']));
    }

    public function testAUrlencodedFormHasNoFiles(): void
    {
        $json = self::patched([
            'content' => [
                'application/x-www-form-urlencoded' => ['schema' => [
                    'type' => 'object',
                    'properties' => ['q' => ['type' => 'string'], 'blob' => ['type' => 'string', 'format' => 'binary'], 'any' => ['type' => 'object']],
                    'required' => ['q'],
                ]],
                'text/csv' => ['schema' => ['type' => 'string']],
            ],
            'required' => true,
        ]);

        $source = self::generate($json)['resource-articles.php'];
        self::assertStringContainsString("(TContentType is 'application/x-www-form-urlencoded' ? array{q: string, blob?: string, any?: string|int|float|bool} : string) \$body", $source);
    }

    private static function action(string $json, string $name): ActionDescriptor
    {
        $action = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource('articles')->actions[$name] ?? null;
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

    /** The witness with bulk-import's request body replaced. */
    private static function patched(mixed $requestBody): string
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        $node = &$document;
        foreach (self::BULK_IMPORT as $key) {
            self::assertIsArray($node);
            self::assertArrayHasKey($key, $node);
            $node = &$node[$key];
        }
        $node = $requestBody;
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
