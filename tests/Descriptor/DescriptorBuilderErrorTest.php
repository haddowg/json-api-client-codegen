<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Descriptor;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * There is no detection layer and no generic mode, so a document the codegen cannot read has to
 * say which structure it wanted. Every message here names a JSON pointer.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(SpecException::class)]
final class DescriptorBuilderErrorTest extends TestCase
{
    public function testAResourceSchemaWithNoTypeConstNamesThePointer(): void
    {
        $document = self::document([
            'AlbumsResource' => ['type' => 'object', 'properties' => ['type' => ['type' => 'string']]],
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsResource.properties.type.const');

        DescriptorBuilder::build($document);
    }

    public function testAResourceSchemaWithNoPropertiesNamesThePointer(): void
    {
        $document = self::document(['AlbumsResource' => ['type' => 'object']]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsResource.properties');

        DescriptorBuilder::build($document);
    }

    public function testANonStringTypeConstNamesThePointerAndTheTypeItFound(): void
    {
        $document = self::document([
            'AlbumsResource' => ['properties' => ['type' => ['const' => ['albums']]]],
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsResource.properties.type.const to be a string, got an array');

        DescriptorBuilder::build($document);
    }

    public function testARelationshipRefToAnUndeclaredComponentNamesIt(): void
    {
        $document = self::document([
            'AlbumsResource' => [
                'properties' => [
                    'type' => ['const' => 'albums'],
                    'relationships' => ['properties' => ['artist' => ['$ref' => '#/components/schemas/AlbumsArtistRelationship']]],
                ],
            ],
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsArtistRelationship');

        DescriptorBuilder::build($document);
    }

    public function testARelationshipWithNoDataNamesThePointer(): void
    {
        $document = self::document([
            'AlbumsResource' => [
                'properties' => [
                    'type' => ['const' => 'albums'],
                    'relationships' => ['properties' => ['artist' => ['$ref' => '#/components/schemas/AlbumsArtistRelationship']]],
                ],
            ],
            'AlbumsArtistRelationship' => ['properties' => ['links' => ['type' => 'object']]],
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsArtistRelationship.properties.data');

        DescriptorBuilder::build($document);
    }

    public function testAnUnrecognisedLinkageShapeIsAnErrorRatherThanADroppedRelation(): void
    {
        $document = self::document([
            'AlbumsResource' => [
                'properties' => [
                    'type' => ['const' => 'albums'],
                    'relationships' => ['properties' => ['artist' => ['$ref' => '#/components/schemas/AlbumsArtistRelationship']]],
                ],
            ],
            'AlbumsArtistRelationship' => ['properties' => ['data' => ['type' => 'string']]],
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage(
            'expected components.schemas.AlbumsArtistRelationship.properties.data to declare linkage as a $ref, an allOf carrying one, or an anyOf or oneOf of them',
        );

        DescriptorBuilder::build($document);
    }

    /**
     * @param array<string, mixed> $schemas
     */
    private static function document(array $schemas): SpecDocument
    {
        return SpecDocument::fromDecoded([
            'openapi' => '3.1.0',
            'paths' => [],
            'components' => ['schemas' => $schemas],
        ]);
    }
}
