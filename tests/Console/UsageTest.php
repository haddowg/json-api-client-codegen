<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Console;

use haddowg\JsonApiCodegen\Console\Usage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Usage::class)]
final class UsageTest extends TestCase
{
    public function testBinaryConstantMatchesTheShippedExecutable(): void
    {
        self::assertSame('json-api-client-codegen', Usage::BINARY);
        self::assertFileExists(\dirname(__DIR__, 2) . '/bin/' . Usage::BINARY);
    }

    public function testTextNamesTheBinaryAndTheInputItExpects(): void
    {
        $text = Usage::text();

        self::assertStringStartsWith(Usage::BINARY, $text);
        self::assertStringContainsString('OpenAPI 3.1', $text);
        self::assertStringContainsString('<openapi-document> <output-directory>', $text);
        self::assertStringEndsWith(\PHP_EOL, $text);
    }

    public function testMusicCatalogFixtureIsAReadableOpenApi31Document(): void
    {
        $path = \dirname(__DIR__) . '/fixtures/music-catalog.openapi.json';

        $raw = \file_get_contents($path);
        self::assertIsString($raw);

        $document = \json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        self::assertSame('3.1.0', $document['openapi'] ?? null);
    }
}
