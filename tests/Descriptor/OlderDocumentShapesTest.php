<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Descriptor;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Shapes that documents from `json-api` before 1.1.0 carry and the current fixture no longer
 * does, pinned against hand-built documents so the codegen keeps reading them.
 */
#[CoversClass(DescriptorBuilder::class)]
final class OlderDocumentShapesTest extends TestCase
{
    public function testAFilterWithAnEmptySchemaIsModelledAsUnknown(): void
    {
        $filters = self::things([
            ['name' => 'filter[title]', 'in' => 'query', 'schema' => []],
            ['name' => 'filter[rating]', 'in' => 'query', 'schema' => ['type' => 'number']],
        ])->filterable;

        self::assertSame(['title', 'rating'], \array_keys($filters));
        self::assertNull($filters['title']->schema, 'an empty schema means the value type is unknown, not string');
        self::assertSame(['number'], $filters['rating']->schema?->types);
    }

    public function testATypeWithNoEndpointsCarriesNoOperations(): void
    {
        $document = SpecDocument::fromDecoded([
            'openapi' => '3.1.0',
            'components' => [
                'schemas' => [
                    'UsersResource' => [
                        'type' => 'object',
                        'properties' => [
                            'type' => ['type' => 'string', 'const' => 'users'],
                            'id' => ['type' => 'string'],
                            'attributes' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
            'paths' => [],
        ]);

        $users = DescriptorBuilder::build($document)->resource('users');
        self::assertNotNull($users);

        self::assertSame([], $users->operations);
        self::assertSame([], $users->attributes);
        self::assertSame(PaginatorKind::None, $users->paginator->kind);
    }

    public function testAnOperationDeclaringNeitherIncludeNorFieldsHasNoProjection(): void
    {
        $things = self::things([['name' => 'filter[title]', 'in' => 'query', 'schema' => ['type' => 'string']]]);

        self::assertNull($things->operation(OperationKind::FetchMany)?->projection);
    }

    public function testADocumentWithoutAnErrorCatalogueHasNoCodes(): void
    {
        $document = SpecDocument::fromDecoded([
            'openapi' => '3.1.0',
            'components' => ['schemas' => ['Error' => ['type' => 'object']]],
            'paths' => [],
        ]);

        self::assertSame([], DescriptorBuilder::build($document)->errors);
    }

    public function testACatalogueVariantThatNarrowsNoCodeNamesItsPointer(): void
    {
        $document = SpecDocument::fromDecoded([
            'openapi' => '3.1.0',
            'components' => ['schemas' => ['BrokenError' => ['allOf' => [['$ref' => '#/components/schemas/Error'], ['properties' => ['status' => ['const' => '400']]]]]]],
            'paths' => [],
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('components.schemas.BrokenError.allOf.1.properties');

        DescriptorBuilder::build($document);
    }

    /**
     * @param list<array<string, mixed>> $parameters
     */
    private static function things(array $parameters): ResourceDescriptor
    {
        $document = SpecDocument::fromDecoded([
            'openapi' => '3.1.0',
            'components' => [
                'schemas' => [
                    'ThingsResource' => ['properties' => ['type' => ['const' => 'things']]],
                    'ThingsCollection' => [
                        'properties' => ['data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ThingsResource']]],
                    ],
                ],
            ],
            'paths' => [
                '/things' => [
                    'get' => [
                        'parameters' => $parameters,
                        'responses' => [
                            '200' => ['content' => ['application/vnd.api+json' => ['schema' => ['$ref' => '#/components/schemas/ThingsCollection']]]],
                        ],
                    ],
                ],
            ],
        ]);

        $things = DescriptorBuilder::build($document)->resource('things');
        self::assertNotNull($things);

        return $things;
    }
}
