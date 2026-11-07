<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Descriptor;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\ErrorCodeDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ErrorSourceKind;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The error catalogue is read off the error documents: each offers shapes, and each shape offers
 * its codes as `const` branches of its `code`. These documents are hand-built around one shape
 * at a time, so what each test changes is the only thing that differs.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(ErrorCodeDescriptor::class)]
final class ErrorCatalogueTest extends TestCase
{
    public function testEachBranchOfAShapeIsACode(): void
    {
        $errors = DescriptorBuilder::build(self::document([
            'Error400Parameter' => self::shape(400, [
                ['const' => 'SORTING_UNRECOGNIZED', 'title' => 'Sorting is unrecognized', 'description' => 'Sort on a declared key.'],
                ['const' => 'FILTERING_UNRECOGNIZED', 'title' => ''],
            ], ['source' => ['type' => 'object', 'required' => ['parameter']]]),
        ], examples: ['SORTING_UNRECOGNIZED' => ['value' => ['errors' => [['detail' => 'Unknown sort "x".']]]]]))->errors;

        self::assertSame(['FILTERING_UNRECOGNIZED', 'SORTING_UNRECOGNIZED'], \array_keys($errors));

        $sorting = $errors['SORTING_UNRECOGNIZED'];
        self::assertSame(400, $sorting->status);
        self::assertSame('Error400Parameter', $sorting->schema);
        self::assertSame('SortingUnrecognizedError', $sorting->class);
        self::assertSame('Sorting is unrecognized', $sorting->title);
        self::assertSame('Sort on a declared key.', $sorting->description);
        self::assertSame('Unknown sort "x".', $sorting->exampleDetail);
        self::assertSame(ErrorSourceKind::Parameter, $sorting->source);

        $filtering = $errors['FILTERING_UNRECOGNIZED'];
        self::assertSame('FILTERING_UNRECOGNIZED', $filtering->title, 'a blank title falls back to the code');
        self::assertNull($filtering->description);
        self::assertNull($filtering->exampleDetail);
    }

    public function testAHeaderSourceIsRead(): void
    {
        $errors = DescriptorBuilder::build(self::document([
            'Error415Header' => self::shape(415, [['const' => 'MEDIA_TYPE_UNSUPPORTED']], ['source' => ['type' => 'object', 'required' => ['header']]]),
        ]))->errors;

        self::assertSame(ErrorSourceKind::Header, $errors['MEDIA_TYPE_UNSUPPORTED']->source);
    }

    public function testAShapesMetaTypesEveryCodeInIt(): void
    {
        $meta = ['type' => 'object', 'properties' => ['retryAfter' => ['type' => 'integer']], 'required' => ['retryAfter'], 'additionalProperties' => false];
        $errors = DescriptorBuilder::build(self::document([
            'Error429Throttled' => self::shape(429, [['const' => 'THROTTLED'], ['const' => 'QUOTA_EXCEEDED']], ['meta' => $meta], ['meta']),
            'Error400Original' => self::shape(400, [['const' => 'BAD_JSON']], ['meta' => ['type' => 'object', 'properties' => ['original' => ['type' => 'string']]]]),
        ]))->errors;

        foreach (['THROTTLED', 'QUOTA_EXCEEDED'] as $code) {
            self::assertTrue($errors[$code]->metaRequired, $code);
            self::assertSame(['retryAfter'], \array_keys($errors[$code]->meta->properties ?? []), $code);
        }
        self::assertSame('ThrottledMeta', $errors['THROTTLED']->metaClass());
        self::assertFalse($errors['BAD_JSON']->metaRequired);
        self::assertNotNull($errors['BAD_JSON']->meta);
    }

    public function testACodeScopedToSomeOperationsRecordsThem(): void
    {
        $document = self::document(
            [
                'Error409' => self::shape(409, [['const' => 'RESOURCE_ID_CONFLICT']]),
                'Error409ArticleLocked' => self::shape(409, [['const' => 'ARTICLE_LOCKED']]),
            ],
            documents: [
                'ErrorDocument409' => ['Error409'],
                'ErrorDocument409WithArticleLocked' => ['Error409', 'Error409ArticleLocked'],
            ],
            paths: [
                '/articles/{id}' => [
                    'patch' => ['responses' => ['409' => ['$ref' => '#/components/responses/ConflictWithArticleLocked']]],
                    'delete' => ['responses' => ['409' => ['$ref' => '#/components/responses/ConflictWithArticleLocked']]],
                    'get' => ['responses' => ['409' => ['$ref' => '#/components/responses/Conflict']]],
                ],
            ],
            responses: [
                'Conflict' => self::response('ErrorDocument409'),
                'ConflictWithArticleLocked' => self::response('ErrorDocument409WithArticleLocked'),
            ],
        );
        $errors = DescriptorBuilder::build($document)->errors;

        self::assertSame(['DELETE /articles/{id}', 'PATCH /articles/{id}'], $errors['ARTICLE_LOCKED']->operations);
        self::assertSame([], $errors['RESOURCE_ID_CONFLICT']->operations, 'a code the shared document offers is raised everywhere');
    }

    public function testACodeEndingInErrorIsNotNamedTwice(): void
    {
        $errors = DescriptorBuilder::build(self::document([
            'Error500' => self::shape(500, [['const' => 'APPLICATION_ERROR'], ['const' => 'upstreamTimeout']]),
        ]))->errors;

        self::assertSame('ApplicationError', $errors['APPLICATION_ERROR']->class);
        self::assertSame('UpstreamTimeoutError', $errors['upstreamTimeout']->class);
    }

    public function testTwoCodesNamingOneClassAreRefused(): void
    {
        $document = self::document([
            'Error409' => self::shape(409, [['const' => 'TRACK_LOCKED'], ['const' => 'TRACK-LOCKED']]),
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('TrackLockedError no other code takes, but TRACK_LOCKED does');

        DescriptorBuilder::build($document);
    }

    public function testACatalogueThatNamesOneComponentPerCodeFailsOnTheCodeItCannotRead(): void
    {
        $document = self::document([
            'TrackLockedError' => ['allOf' => [
                ['$ref' => '#/components/schemas/Error'],
                ['type' => 'object', 'properties' => ['code' => ['type' => 'string', 'const' => 'TRACK_LOCKED'], 'status' => ['type' => 'string', 'const' => '409']]],
            ]],
        ]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.TrackLockedError.allOf.1.properties.code.anyOf');

        DescriptorBuilder::build($document);
    }

    public function testAShapeThatNarrowsNothingNamesItsPointer(): void
    {
        $document = self::document(['Error400' => ['allOf' => [['$ref' => '#/components/schemas/Error']]]]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.Error400.allOf to hold an object narrowing the error\'s code and status');

        DescriptorBuilder::build($document);
    }

    /**
     * @param list<array<string, mixed>> $branches
     * @param array<string, mixed>       $properties
     * @param list<string>               $required
     *
     * @return array<string, mixed>
     */
    private static function shape(int $status, array $branches, array $properties = [], array $required = []): array
    {
        return ['allOf' => [
            ['$ref' => '#/components/schemas/Error'],
            [
                'type' => 'object',
                'properties' => [
                    'code' => ['anyOf' => \array_map(static fn(array $branch): array => ['type' => 'string', ...$branch], $branches)],
                    'status' => ['type' => 'string', 'const' => (string) $status],
                    ...$properties,
                ],
                'required' => ['code', 'status', ...$required],
            ],
        ]];
    }

    /** @return array<string, mixed> */
    private static function response(string $document): array
    {
        return ['description' => 'Error', 'content' => ['application/vnd.api+json' => ['schema' => ['$ref' => '#/components/schemas/' . $document]]]];
    }

    /**
     * A document whose generic `ErrorDocument` offers every shape, unless `$documents` says which
     * error documents offer which shapes.
     *
     * @param array<string, array<string, mixed>> $shapes
     * @param array<string, list<string>>|null    $documents
     * @param array<string, mixed>                $paths
     * @param array<string, mixed>                $responses
     * @param array<string, mixed>                $examples
     */
    private static function document(array $shapes, ?array $documents = null, array $paths = [], array $responses = [], array $examples = []): SpecDocument
    {
        $schemas = ['Error' => ['type' => 'object'], ...$shapes];
        foreach ($documents ?? ['ErrorDocument' => \array_keys($shapes)] as $name => $offered) {
            $schemas[$name] = ['type' => 'object', 'properties' => ['errors' => ['type' => 'array', 'items' => ['anyOf' => [
                ['$ref' => '#/components/schemas/Error'],
                ...\array_map(static fn(string $shape): array => ['$ref' => '#/components/schemas/' . $shape], $offered),
            ]]]]];
        }

        return SpecDocument::fromDecoded([
            'openapi' => '3.1.0',
            'components' => ['schemas' => $schemas, 'responses' => $responses, 'examples' => $examples],
            'paths' => $paths,
        ]);
    }
}
