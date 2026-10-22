<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiCodegen\Tests\Golden\Paginators\PaginatorsClient;

use function PHPStan\Testing\assertType;

/**
 * The page type each read of the `paginators` golden output returns, asserted at level 9.
 *
 * Analysed by `composer phpstan-generated` and never executed: `assertType()` does not exist at
 * runtime. A mismatch is a non-ignorable `phpstan.type` error.
 */
final class PaginatorPickerTypes
{
    public function aMenuQueryReadsTheDefaultArmUntilAPickerChoosesAnother(PaginatorsClient $client): void
    {
        $posts = $client->posts->query();
        assertType('haddowg\JsonApiCodegen\Tests\Golden\Paginators\PostQuery<haddowg\JsonApiCodegen\Tests\Golden\Paginators\PostBase, haddowg\JsonApiClient\Pagination\Cursor>', $posts);
        assertType('haddowg\JsonApiClient\Pagination\Cursor', $posts->get()->_page());

        assertType('haddowg\JsonApiClient\Pagination\PageNumber', $posts->pageByNumbered(2, 10)->get()->_page());
        assertType('haddowg\JsonApiClient\Pagination\Offset', $posts->pageByOffset(limit: 5)->get()->_page());
        assertType('haddowg\JsonApiClient\Pagination\Cursor', $posts->pageByCursor(size: 25)->get()->_page());
    }

    public function theChoiceAndTheNarrowingSurviveEachOtherInEitherOrder(PaginatorsClient $client): void
    {
        $forwards = $client->posts->query()->pageByOffset(0, 10)->withAuthor();
        $backwards = $client->posts->query()->withAuthor()->pageByOffset(0, 10);

        foreach ([$forwards, $backwards] as $query) {
            assertType('haddowg\JsonApiCodegen\Tests\Golden\Paginators\PostQuery<haddowg\JsonApiCodegen\Tests\Golden\Paginators\PostBase&haddowg\JsonApiCodegen\Tests\Golden\Paginators\PostHasAuthor, haddowg\JsonApiClient\Pagination\Offset>', $query);
        }

        $page = $forwards->fields(['posts' => ['title']])->get();
        assertType('haddowg\JsonApiClient\Pagination\Offset', $page->_page());
        assertType('haddowg\JsonApiClient\Pagination\Offset|null', $page->_next()?->_page());
        assertType('(haddowg\JsonApiCodegen\Tests\Golden\Paginators\PostBase&haddowg\JsonApiCodegen\Tests\Golden\Paginators\PostHasAuthor)|null', $forwards->first());
    }

    public function aLaterPickerReplacesAnEarlierOne(PaginatorsClient $client): void
    {
        assertType('haddowg\JsonApiClient\Pagination\PageNumber', $client->posts->query()->pageByCursor(after: 'x')->pageByNumbered(3)->get()->_page());
    }

    public function aRelationReadOfAMenuIsTypedAsItsDefault(PaginatorsClient $client): void
    {
        assertType('haddowg\JsonApiClient\Pagination\Cursor', $client->posts->id('1')->comments()->related()->_page());
    }

    public function eachSingleStrategyReadsItsOwnPage(PaginatorsClient $client): void
    {
        assertType('haddowg\JsonApiClient\Pagination\PageNumber', $client->writers->query()->page(2, 10)->get()->_page());
        assertType('haddowg\JsonApiClient\Pagination\Offset', $client->events->query()->page(0, 10)->get()->_page());
        assertType('haddowg\JsonApiClient\Pagination\Cursor', $client->comments->query()->size(10)->get()->_page());
        assertType('haddowg\JsonApiClient\Pagination\FixedPage', $client->chapters->query()->page(2)->get()->_page());
        assertType('haddowg\JsonApiClient\Pagination\PageNumber', $client->tags->query()->page(2, 10)->get()->_page());
        assertType('haddowg\JsonApiClient\Collections\ResourceCollection<haddowg\JsonApiCodegen\Tests\Golden\Paginators\LogBase>', $client->logs->query()->get());
    }
}
