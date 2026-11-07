<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\UnknownSortException;
use haddowg\JsonApiClient\Resources\Fetcher;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\SortFamilyDescriptor;
use haddowg\JsonApiCodegen\Emit\Reads\ReadSources;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\ArticleBase;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\ArticleQuery;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\Articles;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * A `sort` parameter accepting families of keys lists its fixed keys as the enum branch of an
 * `anyOf`, beside one `pattern` branch per family, and names the families under
 * `x-sort-families`. Each family becomes a `sortBy<Prefix>()` taking the key's suffix, and the
 * loose door accepts a key of any family. The witness's `articles` has three families beside
 * its fixed keys; its reads run from `tests/golden/unions`, the witness generated for PHP 8.3.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(SortFamilyDescriptor::class)]
#[CoversClass(ReadSources::class)]
final class ContractTwentyTest extends TestCase
{
    public function testTheFixedKeysAreReadBesideTheFamilies(): void
    {
        $articles = DescriptorBuilder::build(SpecDocument::fromJson((string) \file_get_contents(Fixtures::path('contract-witness.openapi.json')), 'the witness'))->resource('articles');
        self::assertNotNull($articles);

        self::assertContains('-wordCount', $articles->sortable);
        self::assertSame(['legacy', 'meta', 'custom.fields'], \array_map(static fn(SortFamilyDescriptor $family): string => $family->prefix, $articles->sortFamilies));
        self::assertTrue($articles->sortFamilies[0]->deprecated);
        self::assertSame('~^-?custom\.fields\.(?:[^,]+)$~', $articles->sortFamilies[2]->pattern());
    }

    public function testAnOlderDocumentHasNoFamilies(): void
    {
        $albums = Fixtures::musicCatalogDescriptor()->resource('albums');
        self::assertNotNull($albums);
        self::assertSame([], $albums->sortFamilies);
        self::assertNotSame([], $albums->sortable);
    }

    public function testAFamilyMethodSendsItsKeyAsWritten(): void
    {
        self::assertSame(['-meta.views', 'title'], self::sort(self::query()->sortByMeta('views', true)->sort('title')));
        self::assertSame(['custom.fields.colour'], self::sort(self::query()->sortByCustomFields('colour')));
    }

    public function testAFamilyMethodRefusesASuffixItsPatternRejects(): void
    {
        $this->expectException(UnknownSortException::class);

        self::query()->sortByMeta('Not-Allowed');
    }

    public function testTheLooseDoorAcceptsAFamilyKeyAndRefusesAnythingElse(): void
    {
        self::assertSame(['-legacy.rank', 'title'], self::sort(self::query()->sortRaw(['-legacy.rank', 'title'])));

        $this->expectException(UnknownSortException::class);
        self::query()->sortRaw(['popularity']);
    }

    /**
     * The `articles` query of the generated witness, over a fetcher that is never called.
     *
     * @return ArticleQuery<ArticleBase>
     */
    private static function query(): ArticleQuery
    {
        require_once \dirname(__DIR__) . '/golden/unions/autoload.php';
        $fetcher = (new \ReflectionClass(Fetcher::class))->newInstanceWithoutConstructor();

        return (new Articles($fetcher))->query();
    }

    /**
     * @param ArticleQuery<ArticleBase> $query
     *
     * @return list<string>
     */
    private static function sort(ArticleQuery $query): array
    {
        return $query->toReadQuery()->sort;
    }
}
