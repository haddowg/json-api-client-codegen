<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Collections\PaginatedCollection;
use haddowg\JsonApiClient\Pagination\Cursor;
use haddowg\JsonApiClient\Pagination\Offset;
use haddowg\JsonApiClient\Pagination\Page;
use haddowg\JsonApiClient\Pagination\PageNumber;
use haddowg\JsonApiClient\Query\Query;
use haddowg\JsonApiClient\Query\ReadQuery;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiCodegen\Emit\Reads\ReadsEmitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

/**
 * Runs the generated pagination methods from the committed `paginators` golden output, and checks
 * what they put on the wire.
 *
 * PHPStan proves the page types (`tests/GeneratedTypes`); this proves the keys. A renamed key is
 * a role-named parameter that has to land under the server's own name, and a picker has to send
 * its kind and drop whatever an earlier picker set.
 *
 * The classes are reached by name because the generated namespace is not on this package's
 * autoloader. The golden output targets PHP 8.4, hence the requirement.
 */
#[CoversClass(ReadsEmitter::class)]
#[RequiresPhp('>= 8.4.0')]
final class PaginationBehaviourTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once \dirname(__DIR__) . '/golden/paginators/autoload.php';
    }

    public function testAMenuQueryThatPicksNothingSendsNoPageParameters(): void
    {
        self::assertSame('', self::wire(self::query('Posts')));
    }

    public function testAPickerSendsItsKindAndOnlyTheMembersGiven(): void
    {
        self::assertSame('page[kind]=cursor&page[size]=25', self::wire(self::query('Posts', ['pageByCursor', ['size' => 25]])));
        self::assertSame('page[kind]=numbered', self::wire(self::query('Posts', ['pageByNumbered', []])));
        self::assertSame('page[kind]=offset&page[offset]=20&page[limit]=10', self::wire(self::query('Posts', ['pageByOffset', [20, 10]])));
    }

    public function testALaterPickerReplacesAnEarlierOne(): void
    {
        $query = self::query('Posts', ['pageByCursor', ['after' => 'abc', 'size' => 25]], ['pageByNumbered', [2]]);

        self::assertSame('page[kind]=numbered&page[number]=2', self::wire($query));
    }

    public function testAMenuReadsThePageOfTheStrategyItPicked(): void
    {
        self::assertInstanceOf(Cursor::class, self::page(self::query('Posts')), 'an unpicked read gets the default, `cursor`');
        self::assertInstanceOf(PageNumber::class, self::page(self::query('Posts', ['pageByNumbered', [2]])));
        self::assertInstanceOf(Offset::class, self::page(self::query('Posts', ['pageByOffset', [20]])));
        self::assertInstanceOf(Cursor::class, self::page(self::query('Posts', ['pageByNumbered', [2]], ['pageByCursor', []])));
    }

    public function testRenamedKeysLandUnderTheServersOwnNames(): void
    {
        self::assertSame('page[p]=2&page[per]=10', self::wire(self::query('Writers', ['page', [2, 10]])));
        self::assertSame('page[skip]=20&page[take]=10', self::wire(self::query('Events', ['page', [20, 10]])));
        self::assertSame('page[limit]=10&page[after]=abc', self::wire(self::query('Comments', ['size', [10]], ['after', ['abc']])));
        self::assertSame('page[p]=3', self::wire(self::query('Chapters', ['page', [3]])));
    }

    public function testAnUnstampedCanonicalPaginatorIsInferred(): void
    {
        self::assertSame('page[number]=2&page[size]=10', self::wire(self::query('Tags', ['page', [2, 10]])));
    }

    public function testAStrategyTheCodegenDoesNotKnowGeneratesNoPagingAtAll(): void
    {
        $logs = self::query('Logs');

        self::assertFalse(\method_exists($logs, 'page'));
        self::assertFalse((new \ReflectionMethod($logs, 'pageRaw'))->isPublic(), 'the loose door stays shut too');
    }

    /**
     * A type accessor's fresh query, over a fetcher that is never called, with each call applied.
     *
     * @param array{string, array<int|string, mixed>} ...$calls method and its arguments
     *
     * @return Query<object>
     */
    private static function query(string $accessor, array ...$calls): Query
    {
        $fetcher = (new \ReflectionClass(Fetcher::class))->newInstanceWithoutConstructor();
        $accessor = PaginatorOutputTest::NAMESPACE . '\\' . $accessor;
        $query = self::call(new $accessor($fetcher), 'query', []);

        foreach ($calls as [$method, $arguments]) {
            $query = self::call($query, $method, $arguments);
        }
        self::assertInstanceOf(Query::class, $query);

        return $query;
    }

    /**
     * @param array<int|string, mixed> $arguments
     */
    private static function call(object $on, string $method, array $arguments): object
    {
        $callable = [$on, $method];
        self::assertIsCallable($callable);
        $result = $callable(...$arguments);
        self::assertIsObject($result);

        return $result;
    }

    /**
     * @param Query<object> $query
     */
    private static function wire(Query $query): string
    {
        return \urldecode($query->toReadQuery()->toQueryString());
    }

    /**
     * The page the query's private collector reads from a canned document.
     *
     * @param Query<object> $query
     */
    private static function page(Query $query): Page
    {
        $meta = ['page' => ['currentPage' => 2, 'perPage' => 10, 'offset' => 20, 'limit' => 10]];
        $collection = (new \ReflectionMethod($query, 'collect'))->invoke($query, ResourceContext::of(['data' => [], 'meta' => $meta]), new ReadQuery());
        self::assertInstanceOf(PaginatedCollection::class, $collection);

        return $collection->_page();
    }
}
