<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Spec;

use haddowg\JsonApiCodegen\Spec\Node;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpecDocument::class)]
#[CoversClass(SpecException::class)]
final class SpecDocumentTest extends TestCase
{
    public function testItReadsTheMusicCatalogDocument(): void
    {
        $document = Fixtures::musicCatalog();

        self::assertSame('3.1.0', $document->openapiVersion());
        self::assertArrayHasKey('AlbumsResource', $document->schemas());
        self::assertNotNull($document->pathItem('/albums'));
    }

    public function testPathsAreSortedSoTheDescriptorDoesNotInheritDocumentOrder(): void
    {
        $paths = \array_map(\strval(...), \array_keys(Fixtures::musicCatalog()->paths()));
        $sorted = $paths;
        \sort($sorted, \SORT_STRING);

        self::assertSame($sorted, $paths);
    }

    public function testTheMusicCatalogDocumentDeclaresItsContract(): void
    {
        self::assertSame(9, Fixtures::musicCatalog()->contract());
    }

    public function testAContractIsReadFromTheGeneratorBlock(): void
    {
        $document = SpecDocument::fromDecoded(['info' => ['x-generator' => ['contract' => 4]]]);

        self::assertSame(4, $document->contract());
    }

    public function testANonIntegerContractIsRejected(): void
    {
        $document = SpecDocument::fromDecoded(['info' => ['x-generator' => ['contract' => '4']]]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected info.x-generator.contract to be an integer, got a string');

        $document->contract();
    }

    public function testADocumentWithNoComponentsNamesWhatItIsMissing(): void
    {
        $document = SpecDocument::fromDecoded(['openapi' => '3.1.0', 'paths' => []]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components');

        $document->schemas();
    }

    public function testARefToAnUndeclaredComponentNamesIt(): void
    {
        $document = SpecDocument::fromDecoded(['components' => ['schemas' => []]]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsAttributes');

        $document->follow(Node::root(['$ref' => '#/components/schemas/AlbumsAttributes']));
    }

    public function testARefOutsideTheSchemaComponentsIsRejected(): void
    {
        $document = SpecDocument::fromDecoded(['components' => ['schemas' => []]]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected attributes.$ref to reference #/components/schemas/');

        $document->refName(Node::root(['$ref' => 'https://example.test/schema.json'], 'attributes'));
    }

    public function testDereferenceLeavesAnInlineSchemaAlone(): void
    {
        $document = SpecDocument::fromDecoded(['components' => ['schemas' => []]]);
        $inline = Node::root(['type' => 'object']);

        self::assertSame($inline, $document->dereference($inline));
    }

    public function testASingleEntryAllOfHoldingOnlyARefIsThatRef(): void
    {
        $document = SpecDocument::fromDecoded(['components' => ['schemas' => ['AlbumStatus' => ['type' => 'string', 'enum' => ['draft']]]]]);
        $wrapped = Node::root([
            'description' => 'Where the album sits in its release lifecycle.',
            'allOf' => [['$ref' => '#/components/schemas/AlbumStatus']],
            'example' => 'draft',
            'default' => 'draft',
        ], 'attributes.status');

        self::assertSame('AlbumStatus', $document->refName($wrapped));
        self::assertSame('components.schemas.AlbumStatus', $document->dereference($wrapped)->pointer());
        self::assertSame('attributes.status.allOf.0.$ref', $document->reference($wrapped)?->pointer());
    }

    public function testAnAllOfThatComposesIsNotARef(): void
    {
        $document = SpecDocument::fromDecoded(['components' => ['schemas' => []]]);
        $ref = ['$ref' => '#/components/schemas/AlbumsIdentifier'];

        self::assertNull($document->refName(Node::root(['allOf' => [$ref, ['type' => 'object']]])), 'two entries');
        self::assertNull($document->refName(Node::root(['allOf' => [[...$ref, 'description' => 'narrowed']]])), 'an entry with more than the ref');
        self::assertNull($document->refName(Node::root(['allOf' => [$ref], 'anyOf' => [['type' => 'null']]])), 'beside another combinator');
    }

    public function testUnparseableJsonReportsItsSource(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('could not read music-catalog.json');

        SpecDocument::fromJson('{ not json', 'music-catalog.json');
    }

    public function testAJsonArrayIsNotADocument(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected the document root to be an object, got an array');

        SpecDocument::fromJson('[1, 2]', 'music-catalog.json');
    }

    public function testAnUnreadableFileIsReported(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('could not read /nope/music-catalog.json');

        SpecDocument::fromFile('/nope/music-catalog.json');
    }
}
