<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\GoldenFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * A form body whose schema lists `properties` is narrowed to an array shape of them. Core's
 * witness describes its multipart body as a bare object, so this generates from a copy whose
 * `bulk-import` gives its multipart and urlencoded bodies real fields.
 *
 * `tests/golden/form-fields` is that copy's whole generated output, analysed at level 9 by
 * `composer phpstan-generated` beside `tests/GeneratedTypes/FormFieldTypes.php`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(ActionSource::class)]
final class FormFieldsTest extends TestCase
{
    use GoldenFile;

    public const string NAMESPACE = 'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields';

    public function testEveryGeneratedFileMatchesItsGoldenFile(): void
    {
        foreach (self::generate() as $path => $contents) {
            $this->assertMatchesGolden('form-fields/' . $path, $contents);
        }
    }

    public function testTheGoldenDirectoryHoldsNothingTheCodegenNoLongerGenerates(): void
    {
        $golden = \array_map('basename', \glob(self::goldenPath('form-fields') . '/*.php') ?: []);
        \sort($golden, \SORT_STRING);
        $generated = \array_keys(self::generate());
        \sort($generated, \SORT_STRING);

        self::assertSame($generated, $golden, 'Delete golden files for output the codegen no longer generates.');
    }

    /**
     * @return array<string, string>
     */
    private static function generate(): array
    {
        $json = self::json();

        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness with form fields')),
            new GeneratorConfig(self::NAMESPACE, 'FormFields', PhpTarget::Php84),
            \hash('sha256', $json),
        );
    }

    /** The witness with bulk-import's form bodies given fields. */
    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);
        $document = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        $content = &$document;
        foreach (['paths', '/articles/-actions/bulk-import', 'post', 'requestBody', 'content'] as $key) {
            self::assertIsArray($content);
            self::assertArrayHasKey($key, $content);
            $content = &$content[$key];
        }
        self::assertIsArray($content);
        self::assertArrayHasKey('multipart/form-data', $content);
        $content['multipart/form-data'] = ['schema' => [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'cover' => ['type' => 'string', 'format' => 'binary'],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                'rows' => ['type' => 'integer'],
                'status' => ['type' => 'string', 'enum' => ['draft', 'published']],
            ],
            'required' => ['title'],
        ]];
        $content['application/x-www-form-urlencoded'] = ['schema' => [
            'type' => 'object',
            'properties' => ['title' => ['type' => 'string']],
            'required' => ['title'],
            'additionalProperties' => ['type' => 'integer'],
        ]];
        unset($content);

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }
}
