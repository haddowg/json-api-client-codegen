<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\FilterDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ValueSchema;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Reads\FilterTypes;
use haddowg\JsonApiCodegen\Emit\Reads\ReadsEmitter;
use haddowg\JsonApiCodegen\Emit\Reads\ReadSources;
use haddowg\JsonApiCodegen\Emit\Reads\ReadSurface;
use haddowg\JsonApiCodegen\Emit\Reads\Throws;
use haddowg\JsonApiCodegen\Tests\Support\ArtefactParity;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadsEmitter::class)]
#[CoversClass(ReadSurface::class)]
#[CoversClass(ReadSources::class)]
#[CoversClass(FilterTypes::class)]
#[CoversClass(Throws::class)]
final class ReadsEmitterTest extends TestCase
{
    public function testAlbumProjectingMatchesTheArtefact(): void
    {
        ArtefactParity::assertMatches('AlbumProjecting', 'resource-albums.php', Generated::artefactFile('resource-albums.php'));
    }

    public function testAlbumProjectionMatchesTheArtefact(): void
    {
        ArtefactParity::assertMatches('AlbumProjection', 'resource-albums.php', Generated::artefactFile('resource-albums.php'));
    }

    public function testAlbumQueryMatchesTheArtefact(): void
    {
        ArtefactParity::assertMatches('AlbumQuery', 'resource-albums.php', Generated::artefactFile('resource-albums.php'));
    }

    public function testTheAlbumContributionCarriesTheArtefactsAliasesAndConstantsInOrder(): void
    {
        $contribution = self::contribution(Fixtures::musicCatalogDescriptor(), 'Album');

        self::assertSame(ReadsEmitter::ORDER, $contribution->order);
        self::assertSame([
            '@phpstan-type AlbumRangeShape array{min?: float, max?: float}',
            '@phpstan-type AlbumDateRangeShape array{min?: AlbumDateInput, max?: AlbumDateInput}',
            "@phpstan-type AlbumFilterShape array{tracks?: string, 'artist.name'?: string, title?: string, rating?: AlbumRangeShape, releasedAt?: AlbumDateRangeShape, q?: string}",
            "@phpstan-type AlbumSortToken '-releasedAt'|'-status'|'-title'|'releasedAt'|'status'|'title'",
            "@phpstan-type AlbumIncludePath 'artist'|'artist.albums'|'tracks'|'tracks.album'|'tracks.playlists'",
            "@phpstan-type AlbumFieldToken 'artist'|'artwork'|'availableFrom'|'availableUntil'|'averageRating'|'explicit'|'releaseInfo'|'releasedAt'|'status'|'title'|'tracks'",
            "@phpstan-type AlbumCountToken 'tracks'",
        ], $contribution->tags);

        \preg_match_all('/public const \w+ (\w+) =/', $contribution->members, $constants);
        self::assertSame(['FILTERS', 'FILTER_MEMBERS', 'SORT_TOKENS', 'FIELDS', 'COUNT_TOKENS', 'COUNTABLE_PROFILE'], $constants[1]);
        self::assertStringContainsString("public const array FILTERS = ['tracks', 'artist.name', 'title', 'rating', 'releasedAt', 'q'];", $contribution->members);
        self::assertStringContainsString("'rating' => ['min', 'max'],", $contribution->members);
    }

    public function testAnUnpaginatedUnsortedUnfilteredCollectionGetsOnlyItsTerminalReads(): void
    {
        $query = self::unit(Fixtures::musicCatalogDescriptor(), 'ChartQuery')->source;

        self::assertStringContainsString('@return ResourceCollection<TProj>', $query);
        self::assertStringContainsString('return $this->get()->first();', $query);
        foreach (['function page(', 'function sort(', 'function filter(', 'function withCount(', 'function where', 'PaginatedCollection', 'use ChartProjecting'] as $absent) {
            self::assertStringNotContainsString($absent, $query);
        }

        self::assertNull(self::unitOrNull(Fixtures::musicCatalogDescriptor(), 'ChartProjecting'), 'nothing projects, so there is no trait');
        self::assertNull(self::unitOrNull(Fixtures::musicCatalogDescriptor(), 'ChartProjection'), 'no single-resource read declares include or fields');
    }

    public function testTheFilterValueTypeComesFromTheDocument(): void
    {
        $query = self::unit(Fixtures::musicCatalogDescriptor(), 'TrackQuery')->source;

        self::assertStringContainsString('public function whereExplicit(bool $value): static', $query);
        self::assertStringContainsString("     * @param list<string> \$value\n", $query);
        self::assertStringContainsString('public function whereGenres(array $value): static', $query);
    }

    public function testAFilterWithAnUnknownSchemaTakesMixed(): void
    {
        $api = self::with(['q' => null]);

        self::assertStringContainsString('public function whereQ(mixed $value): static', self::unit($api, 'AlbumQuery')->source);
        self::assertContains('@phpstan-type AlbumFilterShape array{q?: mixed}', self::contribution($api, 'Album')->tags);
    }

    public function testADateBoundNamesTheDateInputAliasOnlyWhereTheWriteSideDeclaresIt(): void
    {
        $date = self::schema(['string'], 'date-time');
        $query = self::unit(self::with(['at' => $date]), 'AlbumQuery')->source;
        self::assertStringContainsString("@param AlbumDateInput \$value\n", $query);
        self::assertStringContainsString('@phpstan-import-type AlbumDateInput from Album', $query);

        $readOnly = self::with(['at' => $date], static fn(ResourceDescriptor $r): array => ['createAttributes' => [], 'updateAttributes' => []]);
        $query = self::unit($readOnly, 'AlbumQuery')->source;
        self::assertStringContainsString('public function whereAt(\DateTimeImmutable|string $value): static', $query);
        self::assertStringNotContainsString('AlbumDateInput', $query);
    }

    public function testFiltersThatNormaliseToOneMethodAreRoutedAndWarned(): void
    {
        $string = self::schema(['string']);
        $api = self::with(['artist.name' => $string, 'artistName' => $string]);
        $emitter = new ReadsEmitter();
        $units = [...$emitter->emit($api, Generated::config())];
        $query = self::find($units, 'AlbumQuery')->source ?? '';

        self::assertStringContainsString("public function whereArtistName(string \$value): static\n    {\n        return \$this->filtering(['artistName' => \$value]);", $query);
        self::assertStringContainsString("public function whereArtistDotName(string \$value): static\n    {\n        return \$this->filtering(['artist.name' => \$value]);", $query);
        self::assertCount(1, $emitter->warnings());
        self::assertStringContainsString('`artist.name`, `artistName` all normalise to whereArtistName()', $emitter->warnings()[0]);
    }

    public function testAFallbackThatTakesAnotherFiltersNaturalNameIsDropped(): void
    {
        $string = self::schema(['string']);
        $emitter = new ReadsEmitter();
        $units = [...$emitter->emit(self::with(['artist.name' => $string, 'artist-name' => $string, 'artistDotName' => $string]), Generated::config())];
        $query = self::find($units, 'AlbumQuery')->source ?? '';

        self::assertStringNotContainsString("['artist.name' => \$value]", $query, 'its fallback is another filter\'s natural name');
        self::assertStringContainsString("public function whereArtistDotName(string \$value): static\n    {\n        return \$this->filtering(['artistDotName' => \$value]);", $query);
        self::assertStringContainsString('public function whereArtistDashName(', $query);
        self::assertStringContainsString("'artist.name'?: string", self::contribution(self::with(['artist.name' => $string, 'artist-name' => $string, 'artistDotName' => $string]), 'Album')->tags[0]);
        self::assertCount(2, $emitter->warnings());
    }

    /**
     * @param array<string, string> $keys    role => wire key
     * @param list<string>          $present
     * @param list<string>          $absent
     */
    #[DataProvider('paginators')]
    public function testEachPaginatorGetsItsOwnPagingMethods(PaginatorKind $kind, array $keys, string $page, string $first, array $present, array $absent): void
    {
        $api = self::with(null, static fn(): array => ['paginator' => new PaginatorDescriptor($kind, $keys)]);
        $query = self::unit($api, 'AlbumQuery')->source;

        self::assertStringContainsString('@return PaginatedCollection<TProj, ' . $page . '>', $query);
        self::assertStringContainsString($page . '::read(', $query);
        self::assertStringContainsString('return ' . $first . ';', $query);
        foreach ($present as $signature) {
            self::assertStringContainsString($signature, $query);
        }
        foreach ($absent as $signature) {
            self::assertStringNotContainsString($signature, $query);
        }
    }

    /**
     * @return iterable<string, array{PaginatorKind, array<string, string>, string, string, list<string>, list<string>}>
     */
    public static function paginators(): iterable
    {
        yield 'fixed' => [PaginatorKind::Fixed, ['number' => 'p'], 'FixedPage', '$this->page(1)->get()->first()', ["public function page(int \$number): static\n    {\n        return \$this->paging(['p' => \$number]);"], ['$size']];
        yield 'page with renamed keys' => [PaginatorKind::Page, ['number' => 'p', 'size' => 'per'], 'PageNumber', '$this->page(1, 1)->get()->first()', ["public function page(int \$number, ?int \$size = null): static\n    {\n        return \$this->paging(\$size === null ? ['p' => \$number] : ['p' => \$number, 'per' => \$size]);"], ["'number' =>", "'size' =>"]];
        yield 'offset' => [PaginatorKind::Offset, ['limit' => 'limit', 'offset' => 'offset'], 'Offset', '$this->page(0, 1)->get()->first()', ['public function page(int $offset, ?int $limit = null): static', '@param int<0, max>'], ['function after(']];
        yield 'offset with renamed keys' => [PaginatorKind::Offset, ['limit' => 'take', 'offset' => 'skip'], 'Offset', '$this->page(0, 1)->get()->first()', ["return \$this->paging(\$limit === null ? ['skip' => \$offset] : ['skip' => \$offset, 'take' => \$limit]);"], ["'offset' =>", "'limit' =>"]];
        yield 'cursor' => [PaginatorKind::Cursor, ['after' => 'after', 'before' => 'before', 'size' => 'size'], 'Cursor', '$this->size(1)->get()->first()', ['public function after(string $cursor): static', 'public function before(string $cursor): static', 'public function size(int $size): static'], ['function page(']];
        yield 'cursor with a renamed size' => [PaginatorKind::Cursor, ['after' => 'after', 'before' => 'before', 'size' => 'limit'], 'Cursor', '$this->size(1)->get()->first()', ["public function size(int \$size): static\n    {\n        return \$this->paging(['limit' => \$size]);"], ["'size' =>"]];
        yield 'cursor with one bound and no size' => [PaginatorKind::Cursor, ['after' => 'after'], 'Cursor', '$this->get()->first()', ['public function after(string $cursor): static'], ['function before(', 'function size(', 'function page(']];
    }

    public function testTwoMenuKindsThatNormaliseToOnePickerKeepTheFirstAndWarn(): void
    {
        $page = new PaginatorDescriptor(PaginatorKind::Page, ['number' => 'number', 'size' => 'size']);
        $cursor = new PaginatorDescriptor(PaginatorKind::Cursor, ['after' => 'after']);
        $api = self::with(null, static fn(): array => ['paginator' => PaginatorDescriptor::menu(['by-date' => $page, 'by_date' => $cursor], 'by-date', 'kind')]);
        $emitter = new ReadsEmitter();
        $query = self::find([...$emitter->emit($api, Generated::config())], 'AlbumQuery')->source ?? '';

        self::assertStringContainsString("return \$this->choose(['kind' => 'by-date', 'number' => \$number, 'size' => \$size], PageNumber::read(...));", $query);
        self::assertSame(1, \substr_count($query, 'public function pageByByDate('));
        self::assertSame(['the `by_date` strategy of `GET /albums` has no picker of its own, because its kind gives the method name `pageByByDate()` that another strategy already has. Select it through pageRaw().'], $emitter->warnings());
    }

    public function testWithCountExistsOnlyWhereTheCollectionCounts(): void
    {
        $api = self::with(null, static fn(): array => ['countable' => null]);

        self::assertStringNotContainsString('withCount', self::unit($api, 'AlbumQuery')->source);
        self::assertStringNotContainsString('COUNT', self::contribution($api, 'Album')->members);
        self::assertStringContainsString("return Request::get(\$query->appendTo('/albums'));", self::unit($api, 'AlbumQuery')->source);
    }

    public function testATypeWithNoCollectionReadHasNoQueryBuilder(): void
    {
        $api = self::with(null, static function (ResourceDescriptor $r): array {
            $operations = $r->operations;
            unset($operations[OperationKind::FetchMany->value]);

            return ['operations' => $operations];
        });

        self::assertNull(self::unitOrNull($api, 'AlbumQuery'));
        self::assertNotNull(self::unitOrNull($api, 'AlbumProjection'), 'the single-resource operations still project');
        self::assertStringNotContainsString('{@see AlbumQuery}', self::unit($api, 'AlbumProjecting')->source);
        self::assertStringNotContainsString('FILTERS', self::contribution($api, 'Album')->members, 'filters belong to the collection read');
    }

    public function testTheGetThrowsListFollowsTheOperationsStatuses(): void
    {
        $query = self::unit(self::with(null, static function (ResourceDescriptor $r): array {
            $operations = $r->operations;
            $read = $operations[OperationKind::FetchMany->value];
            $operations[OperationKind::FetchMany->value] = new OperationDescriptor($read->path, $read->method, [400, 418, 429, 503], $read->projection);

            return ['operations' => $operations];
        }), 'AlbumQuery')->source;

        \preg_match_all('/@throws (\w+)/', \explode('public function get()', $query)[0], $throws);
        self::assertSame(['BadRequest', 'UnexpectedResponse', 'TooManyRequests', 'ServerError', 'TransportException'], \array_slice($throws[1], -5));
    }

    public function testThePhp83TargetEmitsTheSameReadSurface(): void
    {
        $api = Fixtures::musicCatalogDescriptor();
        $on84 = [...(new ReadsEmitter())->emit($api, Generated::config())];
        $on83 = [...(new ReadsEmitter())->emit($api, new GeneratorConfig(Generated::NAMESPACE, 'MusicCatalog', PhpTarget::Php83))];

        self::assertEquals($on84, $on83, 'the read surface has no property hooks to drop');
    }

    /**
     * The generated query builder, run: the loose doors still check names at runtime, nested
     * members included, and the typed doors reach the wire. The golden output targets 8.4.
     */
    #[RequiresPhp('>= 8.4.0')]
    public function testTheGeneratedGuardsRejectUnknownTokensWithASuggestion(): void
    {
        require_once \dirname(__DIR__, 2) . '/golden/generated/autoload.php';
        $fetcher = (new \ReflectionClass(\haddowg\JsonApiClient\Resources\Fetcher::class))->newInstanceWithoutConstructor();
        $class = self::generatedClass('AlbumQuery');
        $query = new $class($fetcher);
        self::assertInstanceOf(\haddowg\JsonApiClient\Query\Query::class, $query);

        $read = self::door(self::door($query, 'filterRaw', ['rating' => ['min' => 4.5]]), 'sortRaw', ['-releasedAt'])->toReadQuery();
        self::assertSame('filter[rating][min]=4.5&sort=-releasedAt', $read->toQueryString());

        foreach ([
            static fn() => self::door($query, 'filterRaw', ['titel' => 'x']),
            static fn() => self::door($query, 'filterRaw', ['rating' => ['minimum' => 4.0]]),
        ] as $call) {
            try {
                $call();
                self::fail('expected an unknown filter to throw');
            } catch (\haddowg\JsonApiClient\Exceptions\UnknownFilterException $e) {
                self::assertStringContainsString('Did you mean', $e->getMessage());
            }
        }

        $this->expectException(\haddowg\JsonApiClient\Exceptions\UnknownSortException::class);
        self::door($query, 'sortRaw', ['+title']);
    }

    /**
     * Call a loose door on a generated query. The runtime keeps the doors protected, so this also
     * proves the generated class opened the one it calls.
     *
     * @param array<array-key, mixed> $argument
     *
     * @return \haddowg\JsonApiClient\Query\Query<object>
     */
    private static function door(object $query, string $method, array $argument): \haddowg\JsonApiClient\Query\Query
    {
        self::assertTrue((new \ReflectionMethod($query, $method))->isPublic(), $method . '() is open on the generated query');
        $result = $query->{$method}($argument);
        self::assertInstanceOf(\haddowg\JsonApiClient\Query\Query::class, $result);

        return $result;
    }

    /**
     * Rebuild the music-catalog descriptor with the `albums` type altered.
     *
     * @param array<string, ValueSchema|null>|null                 $filters replaces the type's filters
     * @param (\Closure(ResourceDescriptor): array<string, mixed>)|null $change  constructor arguments to replace
     */
    private static function with(?array $filters, ?\Closure $change = null): ApiDescriptor
    {
        $api = Fixtures::musicCatalogDescriptor();
        $albums = $api->resources['albums'];
        $args = [
            'type' => $albums->type,
            'attributes' => $albums->attributes,
            'createAttributes' => $albums->createAttributes,
            'updateAttributes' => $albums->updateAttributes,
            'relations' => $albums->relations,
            'operations' => $albums->operations,
            'paginator' => $albums->paginator,
            'clientId' => $albums->clientId,
            'countable' => $albums->countable,
            'includable' => $albums->includable,
            'sortable' => $albums->sortable,
            'filterable' => $albums->filterable,
            'actions' => $albums->actions,
        ];
        if ($filters !== null) {
            $args['filterable'] = [];
            foreach ($filters as $name => $schema) {
                $args['filterable'][$name] = new FilterDescriptor($name, $schema);
            }
        }
        if ($change !== null) {
            $args = [...$args, ...$change($albums)];
        }

        /** @phpstan-ignore argument.type (the named arguments are the constructor's own, rebuilt from an instance) */
        $resource = new ResourceDescriptor(...$args);

        return new ApiDescriptor(['albums' => $resource], $api->enums, $api->atomic, $api->contract, $api->errors);
    }

    /**
     * @return class-string
     */
    private static function generatedClass(string $name): string
    {
        $class = Generated::NAMESPACE . '\\' . $name;
        self::assertTrue(\class_exists($class), $name . ' is not in the golden output');

        return $class;
    }

    /**
     * @param list<string> $types
     */
    private static function schema(array $types, ?string $format = null, ?ValueSchema $items = null): ValueSchema
    {
        return new ValueSchema($types, $format, [], $items, [], [], [], [], [], null);
    }

    private static function unit(ApiDescriptor $api, string $name): ClassUnit
    {
        $unit = self::unitOrNull($api, $name);
        self::assertNotNull($unit, $name . ' was not generated');

        return $unit;
    }

    private static function unitOrNull(ApiDescriptor $api, string $name): ?ClassUnit
    {
        return self::find([...(new ReadsEmitter())->emit($api, Generated::config())], $name);
    }

    /**
     * @param array<ClassUnit|Contribution> $emitted
     */
    private static function find(array $emitted, string $name): ?ClassUnit
    {
        foreach ($emitted as $item) {
            if ($item instanceof ClassUnit && $item->name === $name) {
                return $item;
            }
        }

        return null;
    }

    private static function contribution(ApiDescriptor $api, string $target): Contribution
    {
        foreach ((new ReadsEmitter())->emit($api, Generated::config()) as $item) {
            if ($item instanceof Contribution && $item->target === $target) {
                return $item;
            }
        }

        self::fail('No contribution to ' . $target);
    }
}
