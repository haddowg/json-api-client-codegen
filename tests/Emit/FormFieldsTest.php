<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Tests\Support\GeneratedOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * A form body whose schema lists `properties` is narrowed to an array shape of them. Core's
 * witness describes its multipart body as a bare object, so this generates from a copy whose
 * `bulk-import` gives its multipart and urlencoded bodies real fields.
 *
 * That copy's whole generated output is the `form-fields` suite of `GeneratedOutput`, analysed at level 9 by
 * `composer phpstan-generated` beside `tests/GeneratedTypes/FormFieldTypes.php`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(ActionSource::class)]
final class FormFieldsTest extends TestCase
{
    public const string NAMESPACE = 'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields';

    public function testAFormBodyListingPropertiesIsNarrowedToTheirShape(): void
    {
        $articles = GeneratedOutput::files('form-fields')['resource-articles.php'];

        self::assertStringContainsString("TContentType is 'multipart/form-data' ? array{title: string, cover?: FilePart, tags?: list<string>, rows?: int, status?: 'draft'|'published'}", $articles);
        self::assertStringContainsString("TContentType is 'application/x-www-form-urlencoded' ? array{title: string, ...<string, int>}", $articles);
    }

    public function testTheWitnessAloneLeavesItsBareFormBodyUnnarrowed(): void
    {
        $articles = GeneratedOutput::files('witness')['resource-articles.php'];

        self::assertStringNotContainsString('cover?: FilePart', $articles);
    }
}
