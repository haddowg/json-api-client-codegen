<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * Writes the source of one type's read declarations from its {@see ReadSurface}.
 */
final class ReadSources
{
    private const string RUNTIME = 'haddowg\\JsonApiClient\\';

    /** Local names the generated read methods use, which a type's collection variable must avoid. */
    private const array RESERVED_LOCALS = ['context', 'document', 'query', 'raw', 'request', 'url', 'this'];

    private readonly string $type;

    public function __construct(private readonly ReadSurface $surface)
    {
        $this->type = $surface->descriptor->type;
    }

    public function projecting(): ClassUnit
    {
        $s = $this->surface;
        $name = $s->name('Projecting');
        $users = \array_values(\array_filter([
            $s->projection ? $s->name('Projection') : null,
            $s->collection !== null && $s->queryProjects ? $s->name('Query') : null,
        ]));
        $value = '$' . $this->variable(false);

        $doc = [
            ...Php::wrap(\sprintf(
                'The projection surface of `%s`: the `withX()` include methods and the checked `fields()`, shared by %s.',
                $this->type,
                \implode(' and ', \array_map(static fn(string $user): string => '{@see ' . $user . '}', $users)),
            )),
            '',
            ...Php::wrap('A trait used by final classes rather than a shared base class: `@return static<TProj&Marker>` over a `static`-returning primitive resolves at level 9 only in a final class.'),
            '',
            '@template TProj of object',
            '',
            '@phpstan-require-extends Projection<TProj>',
            '',
            '@generated from the `include` and `fields` parameters of the `' . $this->type . '` read operations',
        ];

        $methods = [];
        foreach ($s->narrowing as $relation) {
            $methods[] = Php::docblock([
                ...Php::wrap(\sprintf('Include the `%s` relation, narrowing the projection so `%s->%s` type-checks.', $relation, $value, Naming::member($relation)), 4),
                '',
                '@return static<TProj&' . Naming::marker($this->type, $relation) . '>',
            ], 4)
                . '    public function ' . Naming::includeMethod($relation) . "(): static\n"
                . "    {\n"
                . '        return $this->including(' . Php::string($relation) . ");\n"
                . "    }\n";
        }

        $imports = [self::RUNTIME . 'Query\\Projection'];
        if ($s->fields !== null) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownFieldException';
            $methods[] = Php::docblock([
                ...Php::wrap(\sprintf('Sparse fieldsets, with the `%s` members checked against the descriptor. Other types\' fieldsets pass through unchecked.', $this->type), 4),
                '',
                '@param array<string, list<string>> $fields',
                '',
                '@return static',
                '',
                \sprintf('@throws UnknownFieldException when `fields[%s]` names a member `%s` does not have', $this->type, $this->type),
            ], 4)
                . "    public function fields(array \$fields): static\n"
                . "    {\n"
                . '        foreach ($fields[' . $s->base . "::TYPE] ?? [] as \$field) {\n"
                . '            if (!\in_array($field, ' . $s->resource . "::FIELDS, true)) {\n"
                . '                throw UnknownFieldException::for(' . $s->base . '::TYPE, $field, ' . $s->resource . "::FIELDS);\n"
                . "            }\n"
                . "        }\n"
                . "\n"
                . "        return parent::fields(\$fields);\n"
                . "    }\n";
        }

        return new ClassUnit(
            $name,
            UnitKind::Trait,
            $s->file,
            Php::docblock($doc, 0, false) . 'trait ' . $name . "\n{\n" . \implode("\n", $methods) . "}\n",
            $imports,
        );
    }

    public function projection(): ClassUnit
    {
        $s = $this->surface;
        $name = $s->name('Projection');

        $doc = [
            ...Php::wrap(\sprintf('What a response carrying `%s` should bring back: `include` and `fields`, and nothing else.', $this->type)),
            '',
            ...Php::wrap('A write response honours exactly these two parameters, so `sort()`, `filter()` and `page()` are not here at all. Reusable: build one and hand it to every operation that should come back the same shape.'),
            '',
            '@template TProj of object',
            '',
            '@extends Projection<TProj>',
            '',
            '@generated from the `include` and `fields` parameters of ' . \implode(', ', \array_map(static fn(string $source): string => '`' . $source . '`', $s->projectionSources)),
        ];

        $body = $s->trait() ? '    /** @use ' . $s->name('Projecting') . "<TProj> */\n    use " . $s->name('Projecting') . ";\n\n" : '';
        $body .= Php::docblock(['An empty projection over the unnarrowed resource.', '', '@return self<' . $s->base . '>'], 4)
            . "    public static function make(): self\n"
            . "    {\n"
            . '        /** @var self<' . $s->base . "> */\n"
            . "        return new self();\n"
            . "    }\n"
            . "\n"
            . Php::docblock([
                ...Php::wrap('Resolve either projection form, a closure or a prebuilt projection, to the query parameters a response carries.', 4),
                '',
                '@template T of object',
                '',
                '@param (callable(self<' . $s->base . '>): self<T>)|self<T>|null $projection',
            ], 4)
            . "    public static function resolve(callable|self|null \$projection): ReadQuery\n"
            . "    {\n"
            . "        if (\$projection === null) {\n"
            . "            return new ReadQuery();\n"
            . "        }\n"
            . "\n"
            . "        return (\$projection instanceof self ? \$projection : \$projection(self::make()))->toReadQuery();\n"
            . "    }\n";

        return new ClassUnit(
            $name,
            UnitKind::FinalClass,
            $s->file,
            Php::docblock($doc, 0, false) . 'final class ' . $name . " extends Projection\n{\n" . $body . "}\n",
            [self::RUNTIME . 'Query\\Projection', self::RUNTIME . 'Query\\ReadQuery'],
        );
    }

    public function query(): ClassUnit
    {
        $s = $this->surface;
        $collection = $s->collection ?? throw new \LogicException('A query builder needs a collection read.');
        $name = $s->name('Query');
        $paginated = $s->paginator->paginated();
        $page = $this->pageClass();

        $imports = [
            self::RUNTIME . 'Http\\Request',
            self::RUNTIME . 'Query\\Query',
            self::RUNTIME . 'Query\\ReadQuery',
            self::RUNTIME . 'Resources\\Fetcher',
            self::RUNTIME . 'Resources\\ResourceContext',
            self::RUNTIME . 'Support\\QueryConditionable',
            self::RUNTIME . 'Collections\\' . ($paginated ? 'PaginatedCollection' : 'ResourceCollection'),
        ];
        if ($page !== null) {
            $imports[] = self::RUNTIME . 'Pagination\\' . $page;
            $imports[] = self::RUNTIME . 'Pagination\\PageLinks';
        }

        $aliases = [];
        $members = [];
        foreach ($s->filters as $filter) {
            if ($filter->method === null) {
                continue;
            }
            \array_push($aliases, ...$filter->type->aliases);
            $members[] = $this->where($filter);
        }
        if ($s->filters !== []) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownFilterException';
            $aliases[] = $s->alias('FilterShape');
            $members[] = $this->filterDoors();
        }
        if ($s->sortable !== []) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownSortException';
            $aliases[] = $s->alias('SortToken');
            $members[] = $this->sortDoors();
        }
        if ($s->countable !== null) {
            $aliases[] = $s->alias('CountToken');
            $members[] = $this->withCount();
        }
        if ($s->queryProjects && $s->includable !== []) {
            $aliases[] = $s->alias('IncludePath');
        }
        \array_push($members, ...$this->paging());

        $throws = Throws::for($collection->errorStatuses);
        \array_push($imports, ...Throws::imports($throws));
        $members[] = $this->terminals($throws);

        if ($s->filters !== []) {
            $members[] = $this->guardFilters();
        }
        $members[] = $this->request($collection->path);
        $members[] = $this->collect();
        $members[] = $this->hydrate();

        $aliases = \array_values(\array_unique($aliases));
        \sort($aliases, \SORT_STRING);

        $doc = [
            ...Php::wrap(\sprintf('A read of `%s %s`: the projection, plus everything only a read can carry.', $collection->method, $collection->path)),
            '',
            ...Php::wrap('Every door the endpoint advertises is here and nothing it does not. Filters come in three doors: a `whereX()` per filter, the typed `filter()` shape, and the loose `filterRaw()`. They are separate methods because a parameter typed as the shape or any array would accept anything. Both array doors still check names at runtime.'),
            '',
            '@template TProj of object',
            '',
            '@extends Query<TProj>',
        ];
        if ($aliases !== []) {
            $doc[] = '';
            foreach ($aliases as $alias) {
                $doc[] = '@phpstan-import-type ' . $alias . ' from ' . $s->resource;
            }
        }
        $doc[] = '';
        $doc[] = '@generated from `' . $collection->method . ' ' . $collection->path . '`';

        $uses = $s->trait() && $s->queryProjects
            ? '    /** @use ' . $s->name('Projecting') . "<TProj> */\n    use " . $s->name('Projecting') . ";\n"
            : '';
        $uses .= "    use QueryConditionable;\n\n    public function __construct(private readonly Fetcher \$fetcher) {}\n";

        return new ClassUnit(
            $name,
            UnitKind::FinalClass,
            $s->file,
            Php::docblock($doc, 0, false) . 'final class ' . $name . " extends Query\n{\n" . $uses . "\n" . \implode("\n", $members) . "}\n",
            $imports,
        );
    }

    public function contribution(): ?Contribution
    {
        $s = $this->surface;
        $tags = [];
        $constants = [];

        foreach ($s->shapes as $shape) {
            $tags[] = '@phpstan-type ' . $shape->name . ' ' . $shape->shape;
        }
        if ($s->filters !== []) {
            $parts = [];
            foreach ($s->filters as $filter) {
                $parts[] = FilterTypes::key($filter->name) . '?: ' . $filter->type->doc;
            }
            $tags[] = '@phpstan-type ' . $s->alias('FilterShape') . ' array{' . \implode(', ', $parts) . '}';

            $constants[] = self::constant(
                ['The filters `' . $this->collectionLabel() . '` accepts. The runtime guard on both array doors checks names against this, and the did-you-mean is drawn from it.', '', '@var list<string>'],
                'array',
                'FILTERS',
                self::list(\array_map(static fn($filter): string => $filter->name, $s->filters), 'FILTERS'),
            );

            if ($s->structuredFilters()) {
                $map = [];
                foreach ($s->filters as $filter) {
                    if ($filter->shape !== null) {
                        $map[] = '        ' . Php::string($filter->name) . ' => [' . \implode(', ', \array_map(Php::string(...), $filter->shape->members)) . "],\n";
                    }
                }
                $constants[] = self::constant(
                    ['The members a structured filter\'s own value accepts. An array shape accepts an unknown key one level down just as it does at the top, so these are checked at runtime too.', '', '@var array<string, list<string>>'],
                    'array',
                    'FILTER_MEMBERS',
                    "[\n" . \implode('', $map) . '    ]',
                );
            }
        }
        if ($s->sortable !== []) {
            $tags[] = '@phpstan-type ' . $s->alias('SortToken') . ' ' . self::union($s->sortable);
            $constants[] = self::constant(
                ['The signed sort tokens `' . $this->collectionLabel() . '` accepts, both directions of each.', '', '@var list<string>'],
                'array',
                'SORT_TOKENS',
                self::list($s->sortable, 'SORT_TOKENS'),
            );
        }
        if ($s->includable !== []) {
            $tags[] = '@phpstan-type ' . $s->alias('IncludePath') . ' ' . self::union($s->includable);
        }
        if ($s->fields !== null) {
            $tags[] = '@phpstan-type ' . $s->alias('FieldToken') . ' ' . self::union($s->fields);
            $constants[] = self::constant(
                ['The members `fields[' . $this->type . ']` may name.', '', '@var list<string>'],
                'array',
                'FIELDS',
                self::list($s->fields, 'FIELDS'),
            );
        }
        if ($s->countable !== null) {
            $tags[] = '@phpstan-type ' . $s->alias('CountToken') . ' ' . self::union($s->countable->tokens);
            $constants[] = self::constant(
                ['The `withCount` tokens `' . $this->collectionLabel() . '` advertises.', '', '@var list<string>'],
                'array',
                'COUNT_TOKENS',
                self::list($s->countable->tokens, 'COUNT_TOKENS'),
            );
            $constants[] = self::constant(
                ['The Countable profile, which the client adds to `Accept` only when a request uses `withCount`.'],
                'string',
                'COUNTABLE_PROFILE',
                Php::string($s->countable->profile),
            );
        }

        if ($tags === [] && $constants === []) {
            return null;
        }

        return new Contribution($s->resource, ReadsEmitter::ORDER, $tags, \implode("\n", $constants));
    }

    private function where(FilterSlot $filter): string
    {
        $param = $filter->shape !== null && $filter->shape->range ? '$range' : '$value';
        $doc = [\sprintf('Filter by `%s`.', $filter->name), ''];
        if (!$filter->type->nativeSuffices()) {
            $doc[] = '@param ' . $filter->type->doc . ' ' . $param;
            $doc[] = '';
        }
        $doc[] = '@return static';

        return Php::docblock($doc, 4)
            . '    public function ' . $filter->method . '(' . $filter->type->native . ' ' . $param . "): static\n"
            . "    {\n"
            . '        return $this->filtering([' . Php::string($filter->name) . ' => ' . $param . "]);\n"
            . "    }\n";
    }

    private function filterDoors(): string
    {
        $shape = $this->surface->alias('FilterShape');

        return Php::docblock([
            ...Php::wrap('The typed array door: values are checked statically, nested ones included, and names at runtime.', 4),
            '',
            '@param ' . $shape . ' $filter',
            '',
            '@return static',
            '',
            '@throws UnknownFilterException when a name is not one this endpoint filters by',
        ], 4)
            . "    public function filter(array \$filter): static\n"
            . "    {\n"
            . "        return \$this->filtering(self::guardFilters(\$filter));\n"
            . "    }\n"
            . "\n"
            . Php::docblock([
                ...Php::wrap('The loose door, for a filter map assembled at runtime. Nothing is checked statically; the names are still checked here.', 4),
                '',
                '@param array<string, mixed> $filter',
                '',
                '@return static',
                '',
                '@throws UnknownFilterException when a name is not one this endpoint filters by',
            ], 4)
            . "    public function filterRaw(array \$filter): static\n"
            . "    {\n"
            . "        return \$this->filtering(self::guardFilters(\$filter));\n"
            . "    }\n";
    }

    private function sortDoors(): string
    {
        $s = $this->surface;

        return Php::docblock([
            ...Php::wrap('Sort by the endpoint\'s own tokens, in precedence order. Argument order is precedence, so nothing is translated between the call and the wire.', 4),
            '',
            '@param ' . $s->alias('SortToken') . ' ...$tokens',
            '',
            '@return static',
        ], 4)
            . "    public function sort(string ...\$tokens): static\n"
            . "    {\n"
            . "        return \$this->sorting(\\array_values(\$tokens));\n"
            . "    }\n"
            . "\n"
            . Php::docblock([
                ...Php::wrap('The loose sort door, for tokens assembled at runtime, which a literal union can never accept.', 4),
                '',
                '@param list<string> $tokens',
                '',
                '@return static',
                '',
                '@throws UnknownSortException when a token is not one this endpoint sorts by',
            ], 4)
            . "    public function sortRaw(array \$tokens): static\n"
            . "    {\n"
            . "        foreach (\$tokens as \$token) {\n"
            . '            if (!\in_array($token, ' . $s->resource . "::SORT_TOKENS, true)) {\n"
            . '                throw UnknownSortException::for($token, ' . $s->resource . "::SORT_TOKENS);\n"
            . "            }\n"
            . "        }\n"
            . "\n"
            . "        return \$this->sorting(\$tokens);\n"
            . "    }\n";
    }

    private function withCount(): string
    {
        $s = $this->surface;

        return Php::docblock([
            ...Php::wrap(\sprintf(
                'Count related resources without including them, under the Countable profile. `%s` advertises %s. A counted relation is still not an included one: its count is read through `_rel()`.',
                $this->collectionLabel(),
                \implode(', ', \array_map(static fn(string $token): string => '`' . $token . '`', $s->countable->tokens ?? [])),
            ), 4),
            '',
            '@param list<' . $s->alias('CountToken') . '> $tokens',
            '',
            '@return static',
        ], 4)
            . "    public function withCount(array \$tokens): static\n"
            . "    {\n"
            . "        return \$this->counting(\$tokens);\n"
            . "    }\n";
    }

    /**
     * The pagination methods of the endpoint's own paginator, and of no other.
     *
     * @return list<string>
     */
    private function paging(): array
    {
        $paginator = $this->surface->paginator;

        return match ($paginator->kind) {
            PaginatorKind::Page => [
                Php::docblock(['@param positive-int      $number', '@param positive-int|null $size', '', '@return static'], 4)
                . "    public function page(int \$number, ?int \$size = null): static\n"
                . "    {\n"
                . "        return \$this->paging(\$size === null ? ['number' => \$number] : ['number' => \$number, 'size' => \$size]);\n"
                . "    }\n",
            ],
            PaginatorKind::Fixed => [
                Php::docblock([...Php::wrap('Select a page. The server fixes the page size, so there is no size to set.', 4), '', '@param positive-int $number', '', '@return static'], 4)
                . "    public function page(int \$number): static\n"
                . "    {\n"
                . '        return $this->paging([' . Php::string($paginator->parameters[0] ?? 'number') . " => \$number]);\n"
                . "    }\n",
            ],
            PaginatorKind::Offset => [
                Php::docblock(['@param int<0, max>       $offset', '@param positive-int|null $limit', '', '@return static'], 4)
                . "    public function page(int \$offset, ?int \$limit = null): static\n"
                . "    {\n"
                . "        return \$this->paging(\$limit === null ? ['offset' => \$offset] : ['offset' => \$offset, 'limit' => \$limit]);\n"
                . "    }\n",
            ],
            PaginatorKind::Cursor => $this->cursorPaging(),
            PaginatorKind::None => [],
        };
    }

    /**
     * @return list<string>
     */
    private function cursorPaging(): array
    {
        $paginator = $this->surface->paginator;
        $methods = [];

        foreach (['after' => 'Resume after a cursor, as a page\'s `_page()->after` hands it back.', 'before' => 'Resume before a cursor, as a page\'s `_page()->before` hands it back.'] as $bound => $prose) {
            if ($paginator->accepts($bound)) {
                $methods[] = Php::docblock([...Php::wrap($prose, 4), '', '@return static'], 4)
                    . '    public function ' . $bound . "(string \$cursor): static\n"
                    . "    {\n"
                    . '        return $this->paging([' . Php::string($bound) . " => \$cursor]);\n"
                    . "    }\n";
            }
        }

        $size = $this->cursorSizeKey();
        if ($size !== null) {
            $methods[] = Php::docblock(['@param positive-int $size', '', '@return static'], 4)
                . "    public function size(int \$size): static\n"
                . "    {\n"
                . '        return $this->paging([' . Php::string($size) . " => \$size]);\n"
                . "    }\n";
        }

        return $methods;
    }

    private function cursorSizeKey(): ?string
    {
        foreach ($this->surface->paginator->parameters as $parameter) {
            if ($parameter !== 'after' && $parameter !== 'before') {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * @param list<array{string, string}> $throws
     */
    private function terminals(array $throws): string
    {
        $s = $this->surface;
        $paginated = $s->paginator->paginated();
        $page = $this->pageClass();
        $collection = $paginated ? 'PaginatedCollection<TProj, ' . $page . '>' : 'ResourceCollection<TProj>';
        $collect = $paginated ? '$this->collect($this->fetcher->read($this->request($query), $query), $query)' : '$this->collect($this->fetcher->read($this->request($query), $query))';

        [$first, $sized] = match ($s->paginator->kind) {
            PaginatorKind::Page => ['$this->page(1, 1)->get()->first()', true],
            PaginatorKind::Fixed => ['$this->page(1)->get()->first()', false],
            PaginatorKind::Offset => ['$this->page(0, 1)->get()->first()', true],
            PaginatorKind::Cursor => $this->cursorSizeKey() !== null ? ['$this->size(1)->get()->first()', true] : ['$this->get()->first()', false],
            PaginatorKind::None => ['$this->get()->first()', false],
        };

        $singular = Naming::singular($this->type);

        return Php::docblock(['Run the read.', '', '@return ' . $collection, '', ...Throws::lines($throws)], 4)
            . '    public function get(): ' . ($paginated ? 'PaginatedCollection' : 'ResourceCollection') . "\n"
            . "    {\n"
            . "        \$query = \$this->toReadQuery();\n"
            . "\n"
            . '        return ' . $collect . ";\n"
            . "    }\n"
            . "\n"
            . Php::docblock([
                ...Php::wrap(\sprintf('The first %s the read matches%s.', $singular, $sized ? ', fetching a single-resource page rather than a whole one' : ''), 4),
                '',
                '@return TProj|null',
                '',
                ...Throws::lines($throws),
            ], 4)
            . "    public function first(): ?object\n"
            . "    {\n"
            . '        return ' . $first . ";\n"
            . "    }\n";
    }

    private function guardFilters(): string
    {
        $s = $this->surface;
        $doc = Php::docblock([
            '@param ' . $s->alias('FilterShape') . '|array<string, mixed> $filter',
            '',
            '@return array<string, mixed>',
            '',
            '@throws UnknownFilterException',
        ], 4);

        if (!$s->structuredFilters()) {
            return $doc
                . "    private static function guardFilters(array \$filter): array\n"
                . "    {\n"
                . "        foreach (\\array_keys(\$filter) as \$name) {\n"
                . "            \$name = (string) \$name;\n"
                . "\n"
                . '            if (!\in_array($name, ' . $s->resource . "::FILTERS, true)) {\n"
                . '                throw UnknownFilterException::for($name, ' . $s->resource . "::FILTERS);\n"
                . "            }\n"
                . "        }\n"
                . "\n"
                . "        return \$filter;\n"
                . "    }\n";
        }

        return $doc
            . "    private static function guardFilters(array \$filter): array\n"
            . "    {\n"
            . "        foreach (\$filter as \$name => \$value) {\n"
            . "            \$name = (string) \$name;\n"
            . "\n"
            . '            if (!\in_array($name, ' . $s->resource . "::FILTERS, true)) {\n"
            . '                throw UnknownFilterException::for($name, ' . $s->resource . "::FILTERS);\n"
            . "            }\n"
            . "\n"
            . '            $members = ' . $s->resource . "::FILTER_MEMBERS[\$name] ?? null;\n"
            . "\n"
            . "            if (\$members === null || !\\is_array(\$value)) {\n"
            . "                continue;\n"
            . "            }\n"
            . "\n"
            . "            \$qualified = \\array_map(static fn(string \$member): string => \$name . '[' . \$member . ']', \$members);\n"
            . "\n"
            . "            foreach (\\array_keys(\$value) as \$member) {\n"
            . "                if (!\\in_array((string) \$member, \$members, true)) {\n"
            . "                    throw UnknownFilterException::for(\$name . '[' . (string) \$member . ']', \$qualified);\n"
            . "                }\n"
            . "            }\n"
            . "        }\n"
            . "\n"
            . "        return \$filter;\n"
            . "    }\n";
    }

    private function request(string $path): string
    {
        $s = $this->surface;

        if ($s->countable === null) {
            return "    private function request(ReadQuery \$query): Request\n"
                . "    {\n"
                . '        return Request::get($query->appendTo(' . Php::string($path) . "));\n"
                . "    }\n";
        }

        return Php::docblock(Php::wrap('The Countable profile is negotiated only when a request actually uses a `withCount` parameter, which is what `x-profile` on that parameter states.', 4), 4)
            . "    private function request(ReadQuery \$query): Request\n"
            . "    {\n"
            . '        $request = Request::get($query->appendTo(' . Php::string($path) . "));\n"
            . "\n"
            . '        return $query->withCount === [] ? $request : $request->withProfiles([' . $s->resource . "::COUNTABLE_PROFILE]);\n"
            . "    }\n";
    }

    private function collect(): string
    {
        $variable = '$' . $this->variable(true);
        $page = $this->pageClass();

        $loop = "        \$document = \$context->document();\n"
            . '        ' . $variable . " = [];\n"
            . "\n"
            . "        foreach (\$context->primaryList() as \$raw) {\n"
            . '            ' . $variable . "[] = \$this->hydrate(\$raw, \$context);\n"
            . "        }\n"
            . "\n";

        if ($page === null) {
            return Php::docblock(['@return ResourceCollection<TProj>'], 4, false)
                . "    private function collect(ResourceContext \$context): ResourceCollection\n"
                . "    {\n"
                . $loop
                . '        return new ResourceCollection(' . $variable . ", \$document->meta(), \$document->links());\n"
                . "    }\n";
        }

        return Php::docblock(['@return PaginatedCollection<TProj, ' . $page . '>'], 4, false)
            . "    private function collect(ResourceContext \$context, ReadQuery \$query): PaginatedCollection\n"
            . "    {\n"
            . $loop
            . "        return new PaginatedCollection(\n"
            . '            ' . $variable . ",\n"
            . '            ' . $page . "::read(\$document->pageMeta(), PageLinks::fromArray(\$document->links())),\n"
            . "            \$document->meta(),\n"
            . "            \$document->links(),\n"
            . "            fn(string \$url): PaginatedCollection => \$this->collect(\$this->fetcher->readUrl(\$url, \$query), \$query),\n"
            . "        );\n"
            . "    }\n";
    }

    private function hydrate(): string
    {
        $s = $this->surface;

        return Php::docblock([
            ...Php::wrap(\sprintf(
                'The one assertion the design cannot avoid. The runtime always builds a `%s`, but `TProj` is a template parameter no bound can prove it satisfies. `ResourceContext::hydrate()` erases the concrete class so the assertion is legal.',
                $s->resource,
            ), 4),
            '',
            '@param array<string, mixed> $raw',
            '',
            '@return TProj',
        ], 4)
            . "    private function hydrate(array \$raw, ResourceContext \$context): object\n"
            . "    {\n"
            . "        /** @var TProj */\n"
            . '        return $context->hydrate($raw, ' . $s->resource . "::_hydrate(...));\n"
            . "    }\n";
    }

    /** The runtime page class for the endpoint's paginator, null when it is not paginated. */
    private function pageClass(): ?string
    {
        return match ($this->surface->paginator->kind) {
            PaginatorKind::Page, PaginatorKind::Fixed => 'PageNumber',
            PaginatorKind::Offset => 'Offset',
            PaginatorKind::Cursor => 'Cursor',
            PaginatorKind::None => null,
        };
    }

    private function collectionLabel(): string
    {
        $collection = $this->surface->collection;

        return $collection === null ? $this->type : $collection->method . ' ' . $collection->path;
    }

    /** The type as a PHP variable name (`$albums`), or its singular (`$album`). */
    private function variable(bool $plural): string
    {
        $name = Naming::member($plural ? $this->type : Naming::singular($this->type));

        return $name === '' || \in_array($name, self::RESERVED_LOCALS, true) ? ($plural ? 'resources' : 'resource') : $name;
    }

    /**
     * @param list<string> $lines
     */
    private static function constant(array $lines, string $type, string $name, string $value): string
    {
        $wrapped = [];
        foreach ($lines as $line) {
            \array_push($wrapped, ...($line === '' || \str_starts_with($line, '@') ? [$line] : Php::wrap($line, 4)));
        }

        return Php::docblock($wrapped, 4, false) . '    public const ' . $type . ' ' . $name . ' = ' . $value . ";\n";
    }

    /**
     * A list literal, on one line where it fits the 100-column width and one member per line
     * where it does not.
     *
     * @param list<string> $values
     */
    private static function list(array $values, string $name): string
    {
        $inline = '[' . \implode(', ', \array_map(Php::string(...), $values)) . ']';
        if (\strlen('    public const array ' . $name . ' = ' . $inline . ';') <= 100) {
            return $inline;
        }

        return "[\n" . \implode('', \array_map(static fn(string $value): string => '        ' . Php::string($value) . ",\n", $values)) . '    ]';
    }

    /**
     * A literal union, sorted so the alias reads the same whatever order the document lists.
     *
     * @param list<string> $values
     */
    private static function union(array $values): string
    {
        $values = \array_values(\array_unique($values));
        \sort($values, \SORT_STRING);

        return \implode('|', \array_map(Php::string(...), $values));
    }
}
