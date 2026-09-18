<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Descriptor;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The music-catalog fixture only exercises page pagination, in the flattened `page[number]` form
 * an older projector emitted. Every other strategy — and the object-parameter form the current
 * projector emits — is pinned here against hand-built documents.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(PaginatorDescriptor::class)]
final class PaginatorDetectionTest extends TestCase
{
    /**
     * @return iterable<string, array{list<string>, PaginatorKind, list<string>}>
     */
    public static function strategies(): iterable
    {
        yield 'page' => [['number', 'size'], PaginatorKind::Page, ['number', 'size']];
        yield 'offset' => [['offset', 'limit'], PaginatorKind::Offset, ['limit', 'offset']];
        yield 'cursor' => [['after', 'before', 'size'], PaginatorKind::Cursor, ['after', 'before', 'size']];
        yield 'cursor with one bound' => [['after', 'size'], PaginatorKind::Cursor, ['after', 'size']];
        yield 'fixed' => [['number'], PaginatorKind::Fixed, ['number']];
        yield 'unpaginated' => [[], PaginatorKind::None, []];
    }

    /**
     * @param list<string> $members
     * @param list<string> $expectedParameters
     */
    #[DataProvider('strategies')]
    public function testTheObjectParameterFormIsRead(array $members, PaginatorKind $kind, array $expectedParameters): void
    {
        $paginator = self::paginatorOf(self::objectPageParameter($members));

        self::assertSame($kind, $paginator->kind);
        self::assertSame($expectedParameters, $paginator->parameters);
    }

    /**
     * @param list<string> $members
     * @param list<string> $expectedParameters
     */
    #[DataProvider('strategies')]
    public function testTheFlattenedFormIsReadIdentically(array $members, PaginatorKind $kind, array $expectedParameters): void
    {
        $paginator = self::paginatorOf(self::flattenedPageParameters($members));

        self::assertSame($kind, $paginator->kind);
        self::assertSame($expectedParameters, $paginator->parameters);
    }

    public function testAFixedPageCollectionIsPaginatedButHasNoSizeControl(): void
    {
        $paginator = self::paginatorOf(self::objectPageParameter(['number']));

        self::assertSame(PaginatorKind::Fixed, $paginator->kind);
        self::assertTrue($paginator->paginated(), 'a fixed page is paginated; it just has no size parameter');
        self::assertTrue($paginator->accepts('number'));
        self::assertFalse($paginator->accepts('size'));
    }

    public function testAFixedPageKeyIsReadFromTheDocumentRatherThanAssumed(): void
    {
        // The server's page key is configurable (`withPageKey()`), so a renamed one still has to
        // be detected — and carried, or the client puts the wrong parameter on the wire.
        $paginator = self::paginatorOf(self::objectPageParameter(['p']));

        self::assertSame(PaginatorKind::Fixed, $paginator->kind);
        self::assertSame(['p'], $paginator->parameters);
        self::assertFalse($paginator->accepts('number'));
    }

    public function testAnUnknownPageMemberSetIsStillAnError(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('to declare page members matching a known paginator, got limit, number');

        self::paginatorOf(self::objectPageParameter(['number', 'limit']));
    }

    public function testASelectableStrategyMenuErrorsRatherThanPickingAnArm(): void
    {
        $menu = [[
            'name' => 'page',
            'in' => 'query',
            'schema' => [
                'oneOf' => [
                    ['type' => 'object', 'properties' => ['number' => ['type' => 'integer'], 'kind' => ['const' => 'page']]],
                    ['type' => 'object', 'properties' => ['after' => ['type' => 'string'], 'kind' => ['const' => 'cursor']]],
                ],
            ],
        ]];

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('a selectable strategy menu is not yet generated');

        self::paginatorOf($menu);
    }

    public function testAPageParameterWithNoSchemaIsTreatedAsUnpaginated(): void
    {
        self::assertSame(PaginatorKind::None, self::paginatorOf([['name' => 'page', 'in' => 'query']])->kind);
    }

    /**
     * The current projector emits one `page` parameter whose schema declares the members.
     *
     * @param list<string> $members
     *
     * @return list<array<string, mixed>>
     */
    private static function objectPageParameter(array $members): array
    {
        if ($members === []) {
            return [];
        }

        $properties = [];
        foreach ($members as $member) {
            $properties[$member] = ['type' => 'integer', 'minimum' => 1];
        }

        return [[
            'name' => 'page',
            'in' => 'query',
            'schema' => ['type' => 'object', 'properties' => $properties],
            'style' => 'deepObject',
            'explode' => true,
        ]];
    }

    /**
     * The flattened form older documents carry, the committed fixture among them.
     *
     * @param list<string> $members
     *
     * @return list<array<string, mixed>>
     */
    private static function flattenedPageParameters(array $members): array
    {
        return \array_map(
            static fn(string $member): array => ['name' => 'page[' . $member . ']', 'in' => 'query', 'schema' => ['type' => 'integer']],
            $members,
        );
    }

    /**
     * @param list<array<string, mixed>> $parameters
     */
    private static function paginatorOf(array $parameters): PaginatorDescriptor
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

        return $things->paginator;
    }
}
