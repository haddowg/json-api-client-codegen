<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Spec\SpecDocument;

/**
 * The documents the suite generates from.
 *
 * The music catalog and core's contract witness are the servers' own exports, read from the
 * packages `package.json` pins (`npm install` puts them in `node_modules`), never from a
 * committed copy. Hand-built documents, which no server exports, stay in `tests/fixtures`.
 *
 * The music catalog is read once per process: it is the reference input for most tests here,
 * and reading it in each one is the difference between a fast suite and a slow one.
 */
final class Fixtures
{
    private static ?SpecDocument $document = null;

    private static ?ApiDescriptor $descriptor = null;

    /** A hand-built document committed under `tests/fixtures`. */
    public static function path(string $name): string
    {
        return \dirname(__DIR__) . '/fixtures/' . $name;
    }

    /** The bundle's music-catalog example, its `default` server. */
    public static function musicCatalogPath(): string
    {
        return self::package('json-api-symfony-specs', 'music-catalog.openapi.json');
    }

    /** Core's contract witness, the whole witness server. */
    public static function witnessPath(): string
    {
        return self::package('json-api-witness', 'contract-witness.json');
    }

    public static function witnessJson(): string
    {
        $json = \file_get_contents(self::witnessPath());
        if ($json === false) {
            throw new \RuntimeException('Could not read ' . self::witnessPath() . '.');
        }

        return $json;
    }

    public static function musicCatalog(): SpecDocument
    {
        return self::$document ??= SpecDocument::fromFile(self::musicCatalogPath());
    }

    public static function musicCatalogDescriptor(): ApiDescriptor
    {
        return self::$descriptor ??= DescriptorBuilder::build(self::musicCatalog());
    }

    private static function package(string $package, string $file): string
    {
        $path = \dirname(__DIR__, 2) . '/node_modules/@haddowg/' . $package . '/' . $file;

        if (!\is_file($path)) {
            throw new \RuntimeException(\sprintf(
                'No %s. The suite reads the documents package.json pins: run `npm install` with a token that can read GitHub Packages (see tests/fixtures/README.md).',
                $path,
            ));
        }

        return $path;
    }
}
