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
}
