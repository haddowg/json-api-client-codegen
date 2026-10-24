<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Spec\SpecDocument;

/**
 * The music-catalog document, read once per process. It is the reference input for every test
 * here, and reading it 850 KB at a time in each one is the difference between a fast suite and
 * a slow one.
 */
final class Fixtures
{
    private static ?SpecDocument $document = null;

    private static ?ApiDescriptor $descriptor = null;

    private static ?ApiDescriptor $artefactDescriptor = null;

    public const string ARTEFACT_DOCUMENT = 'music-catalog-1.3.openapi.json';

    public static function path(string $name): string
    {
        return \dirname(__DIR__) . '/fixtures/' . $name;
    }

    public static function musicCatalog(): SpecDocument
    {
        return self::$document ??= SpecDocument::fromFile(self::path('music-catalog.openapi.json'));
    }

    public static function musicCatalogDescriptor(): ApiDescriptor
    {
        return self::$descriptor ??= DescriptorBuilder::build(self::musicCatalog());
    }

    /**
     * The `haddowg/json-api` 1.3.0 document the reference artefact was written against. The
     * artefact predates contract 5's read nullability and constraints, so parity is checked
     * against the document it describes rather than the current one.
     */
    public static function artefactDescriptor(): ApiDescriptor
    {
        return self::$artefactDescriptor ??= DescriptorBuilder::build(SpecDocument::fromFile(self::path(self::ARTEFACT_DOCUMENT)));
    }
}
