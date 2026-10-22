<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Descriptor;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The music-catalog fixture only exercises page pagination, in the `page` object parameter a
 * contract-2 projector emits with no `x-paginator`. Every other strategy, the `x-paginator` stamp
 * contract 3 adds (renamed keys and menus included), and the flattened `page[number]` form older
 * projectors emitted, is pinned here against hand-built documents.
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
        self::assertSame('number', $paginator->key('number'));
        self::assertNull($paginator->key('size'));
    }

    /**
     * @return iterable<string, array{array{strategy: string, keys: array<string, string>}, PaginatorKind, array<string, string>}>
     */
    public static function stampedStrategies(): iterable
    {
        yield 'page' => [['strategy' => 'page', 'keys' => ['number' => 'p', 'size' => 'per']], PaginatorKind::Page, ['number' => 'p', 'size' => 'per']];
        yield 'offset' => [['strategy' => 'offset', 'keys' => ['offset' => 'skip', 'limit' => 'take']], PaginatorKind::Offset, ['limit' => 'take', 'offset' => 'skip']];
        yield 'cursor' => [['strategy' => 'cursor', 'keys' => ['after' => 'from', 'before' => 'to', 'size' => 'limit']], PaginatorKind::Cursor, ['after' => 'from', 'before' => 'to', 'size' => 'limit']];
        yield 'fixed' => [['strategy' => 'fixed', 'keys' => ['number' => 'p']], PaginatorKind::Fixed, ['number' => 'p']];
        yield 'a role this codegen has no parameter for is ignored' => [['strategy' => 'fixed', 'keys' => ['number' => 'p', 'shard' => 's']], PaginatorKind::Fixed, ['number' => 'p']];
    }

    /**
     * @param array{strategy: string, keys: array<string, string>} $stamp
     * @param array<string, string>                              $keys
     */
    #[DataProvider('stampedStrategies')]
    public function testAStampedStrategyIsReadWithItsRenamedKeys(array $stamp, PaginatorKind $kind, array $keys): void
    {
        $api = self::apiOf(self::stampedPageParameter(\array_values($stamp['keys']), $stamp));

        self::assertSame($kind, self::things($api)->paginator->kind);
        self::assertSame($keys, self::things($api)->paginator->keys);
        self::assertSame([], $api->warnings, 'a renamed key the server stamped is nothing to warn about');
    }

    public function testTheStampDecidesEvenWhereTheNamesSaySomethingElse(): void
    {
        // `after` and `size` alone would infer a cursor. The server says otherwise, and it is right.
        $paginator = self::paginatorOf(self::stampedPageParameter(['after', 'size'], ['strategy' => 'page', 'keys' => ['number' => 'after', 'size' => 'size']]));

        self::assertSame(PaginatorKind::Page, $paginator->kind);
        self::assertSame(['number' => 'after', 'size' => 'size'], $paginator->keys);
    }

    public function testARenamedKeyWithNoStampWarnsAndGeneratesUnpaginated(): void
    {
        // A lone `p` used to be read as fixed. Any single member is not a fixed page: only `number` is.
        $api = self::apiOf(self::objectPageParameter(['p']));

        self::assertSame(PaginatorKind::None, self::things($api)->paginator->kind);
        self::assertSame(['`GET /things` pages by `p`, which match no paginator this codegen recognises by name, and carries no `x-paginator`; its reads are generated unpaginated.'], $api->warnings);
    }

    public function testAnUnknownPageMemberSetWarnsRatherThanFailing(): void
    {
        $api = self::apiOf(self::objectPageParameter(['number', 'limit']));

        self::assertSame(PaginatorKind::None, self::things($api)->paginator->kind);
        self::assertCount(1, $api->warnings);
        self::assertStringContainsString('pages by `limit`, `number`, which match no paginator', $api->warnings[0]);
    }

    public function testAnUnknownFlattenedMemberSetWarnsRatherThanFailing(): void
    {
        $api = self::apiOf(self::flattenedPageParameters(['token', 'window']));

        self::assertSame(PaginatorKind::None, self::things($api)->paginator->kind);
        self::assertStringContainsString('pages by `token`, `window`', $api->warnings[0] ?? '');
    }

    public function testAStrategyThisCodegenDoesNotKnowWarnsAndGeneratesUnpaginated(): void
    {
        $api = self::apiOf(self::stampedPageParameter(['seek'], ['strategy' => 'keyset', 'keys' => ['seek' => 'seek']]));

        self::assertSame(PaginatorKind::None, self::things($api)->paginator->kind);
        self::assertSame(['`GET /things` paginates by the `keyset` strategy, which this codegen does not know, so its reads are generated unpaginated.'], $api->warnings);
    }

    public function testAStampMissingTheKeyItPagesByWarnsAndGeneratesUnpaginated(): void
    {
        $api = self::apiOf(self::stampedPageParameter(['size'], ['strategy' => 'cursor', 'keys' => ['size' => 'size']]));

        self::assertSame(PaginatorKind::None, self::things($api)->paginator->kind);
        self::assertStringContainsString('declares the `cursor` strategy without the key it pages by', $api->warnings[0] ?? '');
    }

    public function testAMenuIsReadArmByArmWithItsDefault(): void
    {
        $paginator = self::paginatorOf(self::menu('numbered', [
            self::arm('numbered', ['strategy' => 'page', 'keys' => ['number' => 'p', 'size' => 'size']]),
            self::arm('offset', ['strategy' => 'offset', 'keys' => ['offset' => 'offset', 'limit' => 'limit']]),
            self::arm('cursor', ['strategy' => 'cursor', 'keys' => ['after' => 'after', 'before' => 'before', 'size' => 'size']]),
        ]));

        self::assertSame(PaginatorKind::Multi, $paginator->kind);
        self::assertSame('numbered', $paginator->default);
        self::assertSame('kind', $paginator->discriminator);
        self::assertSame(['numbered', 'offset', 'cursor'], \array_keys($paginator->arms), 'arms keep the order the server declares them in');
        self::assertSame(PaginatorKind::Page, $paginator->arms['numbered']->kind, 'the kind selects the arm; the strategy says what it is');
        self::assertSame(['number' => 'p', 'size' => 'size'], $paginator->arms['numbered']->keys);
        self::assertSame($paginator->arms['numbered'], $paginator->unchosen(), 'a read that picks nothing gets the default');
    }

    public function testAnUnstampedMenuArmIsInferredFromItsCanonicalNames(): void
    {
        $paginator = self::paginatorOf(self::menu('cursor', [
            self::arm('cursor', null, ['after', 'before', 'size']),
            self::arm('page', null, ['number', 'size']),
        ]));

        self::assertSame(PaginatorKind::Cursor, $paginator->arms['cursor']->kind);
        self::assertSame(PaginatorKind::Page, $paginator->arms['page']->kind);
    }

    public function testAMenuArmThisCodegenCannotReadIsDroppedAndTheRestStayPickable(): void
    {
        $api = self::apiOf(self::menu('page', [
            self::arm('page', ['strategy' => 'page', 'keys' => ['number' => 'number', 'size' => 'size']]),
            self::arm('keyset', ['strategy' => 'keyset', 'keys' => ['seek' => 'seek']]),
        ]));
        $paginator = self::things($api)->paginator;

        self::assertSame(['page'], \array_keys($paginator->arms));
        self::assertSame(['the `keyset` strategy of `GET /things` paginates by the `keyset` strategy, which this codegen does not know, so it cannot be picked and is generated unpaginated.'], $api->warnings);
    }

    public function testAMenuWhoseDefaultCannotBeReadWarnsAndGeneratesUnpaginated(): void
    {
        // An unchosen read is typed as the default's page, so without it there is nothing to type.
        $api = self::apiOf(self::menu('keyset', [
            self::arm('page', ['strategy' => 'page', 'keys' => ['number' => 'number', 'size' => 'size']]),
            self::arm('keyset', ['strategy' => 'keyset', 'keys' => ['seek' => 'seek']]),
        ]));

        self::assertSame(PaginatorKind::None, self::things($api)->paginator->kind);
        self::assertCount(2, $api->warnings);
        self::assertSame('`GET /things` defaults to the `keyset` strategy, which is not one this codegen can read, so its reads are generated unpaginated.', $api->warnings[1]);
    }

    public function testAMenuWithNoStampWarnsAndGeneratesUnpaginated(): void
    {
        // Contract 2 documents carry the menu with nothing naming the default a bare read gets.
        $api = self::apiOf([[
            'name' => 'page',
            'in' => 'query',
            'schema' => [
                'oneOf' => [
                    ['type' => 'object', 'properties' => ['number' => ['type' => 'integer'], 'kind' => ['const' => 'page']]],
                    ['type' => 'object', 'properties' => ['after' => ['type' => 'string'], 'kind' => ['const' => 'cursor']]],
                ],
            ],
        ]]);

        self::assertSame(PaginatorKind::None, self::things($api)->paginator->kind);
        self::assertStringContainsString('offers a menu of pagination strategies with no `x-paginator` naming its default', $api->warnings[0] ?? '');
    }

    public function testAPageParameterWithNoSchemaIsTreatedAsUnpaginated(): void
    {
        self::assertSame(PaginatorKind::None, self::paginatorOf([['name' => 'page', 'in' => 'query']])->kind);
    }

    /**
     * @param list<string>         $members
     * @param array<string, mixed> $stamp
     *
     * @return list<array<string, mixed>>
     */
    private static function stampedPageParameter(array $members, array $stamp): array
    {
        $properties = [];
        foreach ($members as $member) {
            $properties[$member] = ['type' => 'integer', 'minimum' => 1];
        }

        return [[
            'name' => 'page',
            'in' => 'query',
            'schema' => ['type' => 'object', 'properties' => $properties, 'x-paginator' => $stamp],
            'style' => 'deepObject',
            'explode' => true,
        ]];
    }

    /**
     * A `MultiPaginator` page parameter, shaped as core projects one.
     *
     * @param list<array<string, mixed>> $arms
     *
     * @return list<array<string, mixed>>
     */
    private static function menu(string $default, array $arms): array
    {
        return [[
            'name' => 'page',
            'in' => 'query',
            'schema' => [
                'oneOf' => $arms,
                'discriminator' => ['propertyName' => 'kind'],
                'x-paginator' => ['strategy' => 'multi', 'default' => $default],
            ],
            'style' => 'deepObject',
            'explode' => true,
        ]];
    }

    /**
     * One arm of a menu: its stamp's keys (or the given members) plus the `kind` const.
     *
     * @param array{strategy: string, keys: array<string, string>}|null $stamp
     * @param list<string>                                               $members
     *
     * @return array<string, mixed>
     */
    private static function arm(string $kind, ?array $stamp, array $members = []): array
    {
        $properties = [];
        foreach ($stamp === null ? $members : \array_values($stamp['keys']) as $member) {
            $properties[$member] = ['type' => 'integer'];
        }
        $properties['kind'] = ['type' => 'string', 'const' => $kind];

        $arm = ['type' => 'object', 'properties' => $properties, 'additionalProperties' => false];
        if ($stamp !== null) {
            $arm['x-paginator'] = $stamp;
        }

        return $arm;
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
        return self::things(self::apiOf($parameters))->paginator;
    }

    private static function things(ApiDescriptor $api): ResourceDescriptor
    {
        $things = $api->resource('things');
        self::assertNotNull($things);

        return $things;
    }

    /**
     * @param list<array<string, mixed>> $parameters
     */
    private static function apiOf(array $parameters): ApiDescriptor
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

        return DescriptorBuilder::build($document);
    }
}
