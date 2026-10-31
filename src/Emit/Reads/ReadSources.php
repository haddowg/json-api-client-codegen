<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\Constraints;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\ExampleTag;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Narrowing;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\ScopeNote;
use haddowg\JsonApiCodegen\Emit\Surface\TypeSurface;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * Writes the source of one type's read declarations from its {@see ReadSurface}.
 */
final class ReadSources
{
    private const string RUNTIME = 'haddowg\\JsonApiClient\\';

    /** The roles a picker takes per strategy, in parameter order, with each one's native type. */
    private const array PICKER_ROLES = [
        'page' => ['number' => 'int', 'size' => 'int'],
        'fixed' => ['number' => 'int'],
        'offset' => ['offset' => 'int', 'limit' => 'int'],
        'cursor' => ['after' => 'string', 'before' => 'string', 'size' => 'int'],
    ];

    /**
     * The smallest value each integer role takes, which the paginator enforces whether or not
     * the document states it. A page schema's own bounds narrow it further.
     */
    private const array ROLE_FLOORS = ['number' => 1, 'size' => 1, 'limit' => 1, 'offset' => 0];

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
            $s->collection !== null && $s->queryProjects && !$s->menu() ? $s->name('Query') : null,
        ]));

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

        $methods = $this->projectingMethods('');
        $imports = [self::RUNTIME . 'Query\\Projection'];
        if ($s->fields !== null) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownFieldException';
        }

        return new ClassUnit(
            $name,
            UnitKind::Trait,
            $s->file,
            Php::docblock($doc, 0, false) . 'trait ' . $name . "\n{\n" . \implode("\n", $methods) . "}\n",
            $imports,
        );
    }

    /**
     * The `withX()`, `with()` and `fields()` methods, for the trait or written inline into a menu
     * query, whose page template follows the projection through every narrowing. A relation in
     * `$relating` gets the `withX()` that takes a Relationship Query closure.
     *
     * @param list<string> $relating
     *
     * @return list<string>
     */
    private function projectingMethods(string $template, array $relating = []): array
    {
        $s = $this->surface;
        $value = '$' . $this->variable(false);
        $methods = [];
        foreach ($s->narrowing as $relation) {
            if (\in_array($relation, $relating, true)) {
                $methods[] = RelatedQuerySource::withMethod($this->type, $relation, $value, $template);
                continue;
            }
            $methods[] = Php::docblock([
                ...Php::wrap(\sprintf('Include the `%s` relation, narrowing the projection so `%s->%s` type-checks.', $relation, $value, Naming::member($relation)), 4),
                '',
                '@return static<TProj&' . Naming::marker($this->type, $relation) . $template . '>',
            ], 4)
                . '    public function ' . Naming::includeMethod($relation) . "(): static\n"
                . "    {\n"
                . '        return $this->including(' . Php::string($relation) . ");\n"
                . "    }\n";
        }

        if ($s->includable !== []) {
            $methods[] = Php::docblock([
                ...Php::wrap('Include paths assembled at runtime, or deeper than one hop. Nothing is narrowed, so a relation is reached through its always-safe companions or after a runtime check. The runtime keeps this protected; it is public here because an operation declares `include`.', 4),
                '',
                '@return static',
            ], 4)
                . "    public function with(string ...\$paths): static\n"
                . "    {\n"
                . "        return parent::with(...\$paths);\n"
                . "    }\n";
        }

        if ($s->fields !== null) {
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

        return $methods;
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

    /**
     * The projection a single read takes when it can shape an included relation: the write
     * projection's `include` and `fields`, plus the Relationship Query closures a write must not
     * send.
     */
    public function readProjection(): ClassUnit
    {
        $s = $this->surface;
        $name = $s->name('ReadProjection');
        $fetchOne = $s->descriptor->operation(\haddowg\JsonApiCodegen\Descriptor\OperationKind::FetchOne);
        $source = $fetchOne === null ? $this->type : $fetchOne->method . ' ' . $fetchOne->path;

        $doc = [
            ...Php::wrap(\sprintf('What a single read of `%s` should bring back: `include` and `fields`, and how an included relation is filtered and sorted under the Relationship Queries profile.', $this->type)),
            '',
            ...Php::wrap('Kept apart from the write projection because a write does not accept `relatedQuery`. A write projection still serves a read, so one built for writes can be reused here.'),
            '',
            '@template TProj of object',
            '',
            '@extends Projection<TProj>',
            '',
            '@generated from the `include`, `fields` and `relatedQuery` parameters of `' . $source . '`',
        ];

        $body = $s->trait() ? '    /** @use ' . $s->name('Projecting') . "<TProj> */\n    use " . $s->name('Projecting') . ";\n\n" : '';
        foreach ($s->readRelating as $relation) {
            $body .= RelatedQuerySource::withMethod($this->type, $relation->name, '$' . $this->variable(false), '') . "\n";
        }
        $projection = $s->name('Projection');
        $accepts = $s->projection ? '|' . $projection . '<T>' : '';
        $body .= Php::docblock(['An empty projection over the unnarrowed resource.', '', '@return self<' . $s->base . '>'], 4)
            . "    public static function make(): self\n"
            . "    {\n"
            . '        /** @var self<' . $s->base . "> */\n"
            . "        return new self();\n"
            . "    }\n"
            . "\n"
            . Php::docblock([
                ...Php::wrap('Resolve any projection form, a closure, a prebuilt read projection or a write projection, to the query parameters the read carries.', 4),
                '',
                '@template T of object',
                '',
                '@param (callable(self<' . $s->base . '>): self<T>)|self<T>' . $accepts . '|null $projection',
            ], 4)
            . '    public static function resolve(callable|self' . ($s->projection ? '|' . $projection : '') . "|null \$projection): ReadQuery\n"
            . "    {\n"
            . "        if (\$projection === null) {\n"
            . "            return new ReadQuery();\n"
            . "        }\n"
            . "\n"
            . "        return (\$projection instanceof Projection ? \$projection : \$projection(self::make()))->toReadQuery();\n"
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

        $imports = [
            self::RUNTIME . 'Http\\Request',
            self::RUNTIME . 'Query\\Query',
            self::RUNTIME . 'Query\\ReadQuery',
            self::RUNTIME . 'Resources\\Fetcher',
            self::RUNTIME . 'Resources\\ResourceContext',
            self::RUNTIME . 'Support\\QueryConditionable',
            self::RUNTIME . 'Collections\\' . ($paginated ? 'PaginatedCollection' : 'ResourceCollection'),
        ];
        foreach ($this->pageClasses() as $page) {
            $imports[] = self::RUNTIME . 'Pagination\\' . $page;
        }
        if ($paginated) {
            $imports[] = self::RUNTIME . 'Pagination\\PageLinks';
        }

        $aliases = [];
        $members = [];
        $relating = \array_map(static fn($relation): string => $relation->name, $s->queryRelating);
        if ($s->menu() && $s->queryProjects && $s->projects()) {
            \array_push($members, ...$this->projectingMethods(', TPage', $relating));
            if ($s->fields !== null) {
                $imports[] = self::RUNTIME . 'Exceptions\\UnknownFieldException';
            }
        }
        if (!$s->menu()) {
            foreach ($s->queryRelating as $relation) {
                $members[] = RelatedQuerySource::withMethod($this->type, $relation->name, '$' . $this->variable(false), '');
            }
        }
        foreach ($s->filters as $filter) {
            if ($filter->method === null) {
                continue;
            }
            \array_push($aliases, ...$filter->type->aliases);
            $members[] = self::where($filter);
        }
        if ($s->filters !== []) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownFilterException';
            $aliases[] = $s->alias('FilterShape');
            $members[] = self::filterDoors($s->alias('FilterShape'));
        }
        if ($s->sortable !== []) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownSortException';
            $aliases[] = $s->alias('SortToken');
            $members[] = self::sortDoors($s->alias('SortToken'), $s->resource);
        }
        if ($s->countable !== null) {
            $aliases[] = $s->alias('CountToken');
            $members[] = $this->withCount();
        }
        if ($s->queryProjects && $s->includable !== []) {
            $aliases[] = $s->alias('IncludePath');
        }
        \array_push($members, ...$this->paging(), ...$this->pageRaw());

        $throws = Throws::for($collection->errorStatuses);
        \array_push($imports, ...Throws::imports($throws));
        $members[] = $this->terminals($throws, ExampleTag::lines($collection->examples), $collection->scopes);

        if ($s->filters !== []) {
            $members[] = self::guardFilters($s->alias('FilterShape'), $s->resource, $s->structuredFilters());
        }
        $members[] = $this->request($collection->path);
        $members[] = $this->collect();
        $members[] = $this->hydrate();

        $aliases = \array_values(\array_unique($aliases));
        \sort($aliases, \SORT_STRING);

        $doc = [
            ...Php::wrap(\sprintf('A read of `%s %s`: the projection, plus everything only a read can carry.', $collection->method, $collection->path)),
            '',
            ...Php::wrap('Every door the endpoint advertises is here and nothing it does not.' . ($s->filters === [] ? '' : ' Filters come in three doors: a `whereX()` per filter, the typed `filter()` shape, and the loose `filterRaw()`. They are separate methods because a parameter typed as the shape or any array would accept anything. Both array doors still check names at runtime.')),
            '',
            ...($s->menu() ? [
                ...Php::wrap(\sprintf('The endpoint offers a menu of pagination strategies. `TPage` is the page a read returns: the server\'s default, `%s`, until a `pageByX()` picker chooses another.', $s->paginator->default ?? '')),
                '',
            ] : []),
            '@template TProj of object',
            ...($s->menu() ? ['', '@template TPage of Page'] : []),
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

        $uses = $s->trait() && $s->queryProjects && !$s->menu()
            ? '    /** @use ' . $s->name('Projecting') . "<TProj> */\n    use " . $s->name('Projecting') . ";\n"
            : '';
        $uses .= "    use QueryConditionable;\n\n";
        $uses .= $s->menu()
            ? Php::docblock(['@param \\Closure(array<string, mixed>, PageLinks): TPage $reader'], 4, false)
                . "    public function __construct(\n        private readonly Fetcher \$fetcher,\n        private \\Closure \$reader,\n    ) {}\n"
            : "    public function __construct(private readonly Fetcher \$fetcher) {}\n";

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

        if ($s->relatedQueryProfile !== null) {
            $constants[] = self::constant(
                ['The Relationship Queries profile, which the client adds to `Accept` only when a read shapes an included relation.'],
                'string',
                'RELATIONSHIP_QUERIES_PROFILE',
                Php::string($s->relatedQueryProfile),
            );
        }

        if ($tags === [] && $constants === []) {
            return null;
        }

        return new Contribution($s->resource, ReadsEmitter::ORDER, $tags, \implode("\n", $constants));
    }

    public static function where(FilterSlot $filter): string
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

    public static function filterDoors(string $shape): string
    {
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

    /**
     * @param string $owner the class whose `SORT_TOKENS` the loose door checks against
     */
    public static function sortDoors(string $tokenAlias, string $owner): string
    {
        return Php::docblock([
            ...Php::wrap('Sort by the endpoint\'s own tokens, in precedence order. Argument order is precedence, so nothing is translated between the call and the wire.', 4),
            '',
            '@param ' . $tokenAlias . ' ...$tokens',
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
            . '            if (!\in_array($token, ' . $owner . "::SORT_TOKENS, true)) {\n"
            . '                throw UnknownSortException::for($token, ' . $owner . "::SORT_TOKENS);\n"
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
     * Parameters are named by role and the body puts the server's configured key on the wire, so
     * a renamed `page[p]` changes the request and not the call.
     *
     * @return list<string>
     */
    private function paging(): array
    {
        $paginator = $this->surface->paginator;

        return match ($paginator->kind) {
            PaginatorKind::Page => [$this->numbered($paginator, 'number', 'size', null)],
            PaginatorKind::Fixed => [$this->numbered($paginator, 'number', 'size', 'Select a page. The server fixes the page size, so there is no size to set.')],
            PaginatorKind::Offset => [$this->numbered($paginator, 'offset', 'limit', null)],
            PaginatorKind::Cursor => $this->cursorPaging(),
            PaginatorKind::Multi => $this->pickers(),
            PaginatorKind::None => [],
        };
    }

    /**
     * `page()` for a strategy addressed by one position and an optional size: page and fixed by
     * `number`, offset by `offset`. The size parameter exists only where the server reads one.
     */
    private function numbered(PaginatorDescriptor $paginator, string $position, string $size, ?string $prose): string
    {
        $positionKey = Php::string($paginator->key($position) ?? $position);
        $sizeKey = $paginator->key($size);
        $positionType = self::roleType($paginator, $position);
        $sizeType = self::roleType($paginator, $size) . '|null';
        $defaults = self::defaults($paginator, $sizeKey === null ? [$position] : [$position, $size]);
        $prose = $prose === null ? $defaults : ($defaults === null ? $prose : $prose . ' ' . $defaults);
        $doc = $prose === null ? [] : [...Php::wrap($prose, 4), ''];

        if ($sizeKey === null) {
            return Php::docblock([...$doc, '@param ' . $positionType . ' $' . $position, '', '@return static'], 4)
                . '    public function page(int $' . $position . "): static\n"
                . "    {\n"
                . '        return $this->paging([' . $positionKey . ' => $' . $position . "]);\n"
                . "    }\n";
        }

        $width = \max(\strlen($positionType), \strlen($sizeType));

        return Php::docblock([
            ...$doc,
            '@param ' . \str_pad($positionType, $width) . ' $' . $position,
            '@param ' . \str_pad($sizeType, $width) . ' $' . $size,
            '',
            '@return static',
        ], 4)
            . '    public function page(int $' . $position . ', ?int $' . $size . " = null): static\n"
            . "    {\n"
            . '        return $this->paging($' . $size . ' === null ? [' . $positionKey . ' => $' . $position . '] : [' . $positionKey . ' => $' . $position . ', ' . Php::string($sizeKey) . ' => $' . $size . "]);\n"
            . "    }\n";
    }

    /**
     * One picker per arm of a menu, and the private `choose()` they share.
     *
     * Every parameter is optional: picking a strategy is meaningful on its own, and the server
     * fills in whatever a request leaves out. The discriminator goes on the wire even for the
     * default arm, because picking says so explicitly.
     *
     * @return list<string>
     */
    private function pickers(): array
    {
        $s = $this->surface;
        $menu = $s->paginator;
        $methods = [];

        foreach ($s->pickers as $method => $kind) {
            $arm = $menu->arms[$kind];
            $page = TypeSurface::pageClass($arm) ?? throw new \LogicException('A menu arm always paginates.');
            $members = [Php::string($menu->discriminator) . ' => ' . Php::string($kind)];
            $params = [];
            $signature = [];

            $roles = [];
            foreach (self::PICKER_ROLES[$arm->kind->value] ?? [] as $role => $native) {
                $key = $arm->key($role);
                if ($key === null) {
                    continue;
                }
                $roles[] = $role;
                $members[] = Php::string($key) . ' => $' . $role;
                $params[] = [self::roleType($arm, $role) . '|null', '$' . $role];
                $signature[] = '?' . $native . ' $' . $role . ' = null';
            }
            $defaults = self::defaults($arm, $roles);

            $width = \max([0, ...\array_map(static fn(array $param): int => \strlen($param[0]), $params)]);
            $doc = [
                ...Php::wrap(\sprintf(
                    'Page by the `%s` strategy%s. Sends `page[%s]=%s` with whichever members are given, replacing whatever an earlier picker set, and reads `%s` pages.',
                    $kind,
                    $kind === $menu->default ? ', the one the server uses when a read picks none' : '',
                    $menu->discriminator,
                    $kind,
                    $page,
                ) . ($defaults === null ? '' : ' ' . $defaults), 4),
                '',
            ];
            foreach ($params as [$type, $name]) {
                $doc[] = '@param ' . \str_pad($type, $width) . ' ' . $name;
            }
            if ($params !== []) {
                $doc[] = '';
            }
            $doc[] = '@return self<TProj, ' . $page . '>';

            $methods[] = Php::docblock($doc, 4)
                . '    public function ' . $method . '(' . \implode(', ', $signature) . "): self\n"
                . "    {\n"
                . '        return $this->choose([' . \implode(', ', $members) . '], ' . $page . "::read(...));\n"
                . "    }\n";
        }

        $methods[] = Php::docblock([
            ...Php::wrap('Choose a strategy: replace the page parameters with its own, dropping those left null, and re-type the query to read its page. The runtime hands the replaced query back as the base `Query`, which is what makes narrowing it to the new page type legal.', 4),
            '',
            '@template TNext of Page',
            '',
            '@param array<string, mixed>                              $page',
            '@param \\Closure(array<string, mixed>, PageLinks): TNext $reader',
            '',
            '@return self<TProj, TNext>',
        ], 4)
            . "    private function choose(array \$page, \\Closure \$reader): self\n"
            . "    {\n"
            . "        /** @var self<TProj, TNext> \$chosen */\n"
            . "        \$chosen = \$this->repaging(\\array_filter(\$page, static fn(mixed \$value): bool => \$value !== null));\n"
            . "        \$chosen->reader = \$reader;\n"
            . "\n"
            . "        return \$chosen;\n"
            . "    }\n";

        return $methods;
    }

    /**
     * The loose paging door, generated only where the endpoint is paginated: the runtime keeps
     * it protected so an unpaginated type has none.
     *
     * @return list<string>
     */
    private function pageRaw(): array
    {
        if ($this->surface->paginator->kind === PaginatorKind::None) {
            return [];
        }

        return [
            Php::docblock([
                ...Php::wrap(\sprintf('Paginator parameters assembled at runtime. Public here because `%s` is paginated; the runtime keeps it protected.', $this->collectionLabel()), 4),
                '',
                '@param array<string, mixed> $page',
                '',
                '@return static',
            ], 4)
            . "    public function pageRaw(array \$page): static\n"
            . "    {\n"
            . "        return parent::pageRaw(\$page);\n"
            . "    }\n",
        ];
    }

    /**
     * @return list<string>
     */
    private function cursorPaging(): array
    {
        $paginator = $this->surface->paginator;
        $methods = [];

        foreach (['after' => 'Resume after a cursor, as a page\'s `_page()->after` hands it back.', 'before' => 'Resume before a cursor, as a page\'s `_page()->before` hands it back.'] as $bound => $prose) {
            $key = $paginator->key($bound);
            if ($key !== null) {
                $methods[] = Php::docblock([...Php::wrap($prose, 4), '', '@return static'], 4)
                    . '    public function ' . $bound . "(string \$cursor): static\n"
                    . "    {\n"
                    . '        return $this->paging([' . Php::string($key) . " => \$cursor]);\n"
                    . "    }\n";
            }
        }

        $size = $this->cursorSizeKey();
        if ($size !== null) {
            $defaults = self::defaults($paginator, ['size']);
            $methods[] = Php::docblock([...($defaults === null ? [] : [...Php::wrap($defaults, 4), '']), '@param ' . self::roleType($paginator, 'size') . ' $size', '', '@return static'], 4)
                . "    public function size(int \$size): static\n"
                . "    {\n"
                . '        return $this->paging([' . Php::string($size) . " => \$size]);\n"
                . "    }\n";
        }

        return $methods;
    }

    /**
     * A role's PHPDoc type: its floor, narrowed by whatever bounds its page schema declares, such
     * as the cap on a page size.
     */
    private static function roleType(PaginatorDescriptor $paginator, string $role): string
    {
        $floor = self::ROLE_FLOORS[$role] ?? null;

        return $floor === null ? 'string' : Narrowing::integer($paginator->bounds($role)->orElse(new Constraints(minimum: $floor)));
    }

    /**
     * The values the server gives the roles a request leaves out, as a sentence, or null when
     * the document states none.
     *
     * @param list<string> $roles
     */
    private static function defaults(PaginatorDescriptor $paginator, array $roles): ?string
    {
        $parts = [];
        foreach ($roles as $role) {
            $default = $paginator->bounds($role)->default;
            if ($default !== null && !\is_bool($default)) {
                $parts[] = '`$' . $role . '` defaults to ' . $default;
            }
        }

        return $parts === [] ? null : \ucfirst(\implode(' and ', $parts)) . '.';
    }

    private function cursorSizeKey(): ?string
    {
        return $this->surface->paginator->key('size');
    }

    /**
     * @param list<array{string, string}> $throws
     * @param list<string>                $example the `@example` lines for `get()`
     * @param list<string>|null           $scopes  the OAuth scopes the read needs
     */
    private function terminals(array $throws, array $example, ?array $scopes): string
    {
        $s = $this->surface;
        $paginated = $s->paginator->paginated();
        $collection = $paginated ? 'PaginatedCollection<TProj, ' . $this->pageType() . '>' : 'ResourceCollection<TProj>';
        $collect = $paginated ? '$this->collect($this->fetcher->read($this->request($query), $query), $query)' : '$this->collect($this->fetcher->read($this->request($query), $query))';

        $sizes = static fn(string $role): bool => $s->paginator->key($role) !== null;
        [$first, $sized] = match ($s->paginator->kind) {
            PaginatorKind::Page => $sizes('size') ? ['$this->page(1, 1)->get()->first()', true] : ['$this->page(1)->get()->first()', false],
            PaginatorKind::Fixed => ['$this->page(1)->get()->first()', false],
            PaginatorKind::Offset => $sizes('limit') ? ['$this->page(0, 1)->get()->first()', true] : ['$this->page(0)->get()->first()', false],
            PaginatorKind::Cursor => $sizes('size') ? ['$this->size(1)->get()->first()', true] : ['$this->get()->first()', false],
            PaginatorKind::Multi, PaginatorKind::None => ['$this->get()->first()', false],
        };

        $singular = Naming::singular($this->type);

        return Php::docblock(ScopeNote::insert(['Run the read.', '', '@return ' . $collection, '', ...Throws::lines($throws), ...$example], $scopes), 4)
            . '    public function get(): ' . ($paginated ? 'PaginatedCollection' : 'ResourceCollection') . "\n"
            . "    {\n"
            . "        \$query = \$this->toReadQuery();\n"
            . "\n"
            . '        return ' . $collect . ";\n"
            . "    }\n"
            . "\n"
            . Php::docblock(ScopeNote::insert([
                ...Php::wrap(\sprintf('The first %s the read matches%s.', $singular, $sized ? ', fetching a single-resource page rather than a whole one' : ''), 4),
                '',
                '@return TProj|null',
                '',
                ...Throws::lines($throws),
            ], $scopes), 4)
            . "    public function first(): ?object\n"
            . "    {\n"
            . '        return ' . $first . ";\n"
            . "    }\n";
    }

    /**
     * @param string $owner the class whose `FILTERS` and `FILTER_MEMBERS` the guard checks against
     */
    public static function guardFilters(string $shape, string $owner, bool $structured): string
    {
        $doc = Php::docblock([
            '@param ' . $shape . '|array<string, mixed> $filter',
            '',
            '@return array<string, mixed>',
            '',
            '@throws UnknownFilterException',
        ], 4);

        if (!$structured) {
            return $doc
                . "    private static function guardFilters(array \$filter): array\n"
                . "    {\n"
                . "        foreach (\\array_keys(\$filter) as \$name) {\n"
                . "            \$name = (string) \$name;\n"
                . "\n"
                . '            if (!\in_array($name, ' . $owner . "::FILTERS, true)) {\n"
                . '                throw UnknownFilterException::for($name, ' . $owner . "::FILTERS);\n"
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
            . '            if (!\in_array($name, ' . $owner . "::FILTERS, true)) {\n"
            . '                throw UnknownFilterException::for($name, ' . $owner . "::FILTERS);\n"
            . "            }\n"
            . "\n"
            . '            $members = ' . $owner . "::FILTER_MEMBERS[\$name] ?? null;\n"
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
        $profiles = [];
        if ($s->countable !== null) {
            $profiles[] = ['$query->withCount', 'COUNTABLE_PROFILE'];
        }
        if ($s->queryRelating !== []) {
            $profiles[] = ['$query->relatedQuery', 'RELATIONSHIP_QUERIES_PROFILE'];
        }

        if ($profiles === []) {
            return "    private function request(ReadQuery \$query): Request\n"
                . "    {\n"
                . '        return Request::get($query->appendTo(' . Php::string($path) . "));\n"
                . "    }\n";
        }

        if (\count($profiles) === 1 && $profiles[0][1] === 'COUNTABLE_PROFILE') {
            return Php::docblock(Php::wrap('The Countable profile is negotiated only when a request actually uses a `withCount` parameter, which is what `x-profile` on that parameter states.', 4), 4)
                . "    private function request(ReadQuery \$query): Request\n"
                . "    {\n"
                . '        $request = Request::get($query->appendTo(' . Php::string($path) . "));\n"
                . "\n"
                . '        return $query->withCount === [] ? $request : $request->withProfiles([' . $s->resource . "::COUNTABLE_PROFILE]);\n"
                . "    }\n";
        }

        $body = '';
        foreach ($profiles as [$member, $constant]) {
            $body .= '        if (' . $member . " !== []) {\n"
                . '            $profiles[] = ' . $s->resource . '::' . $constant . ";\n"
                . "        }\n";
        }

        return Php::docblock(Php::wrap('Each profile is negotiated only when a request actually uses a parameter it gates, which is what `x-profile` on that parameter states.', 4), 4)
            . "    private function request(ReadQuery \$query): Request\n"
            . "    {\n"
            . '        $request = Request::get($query->appendTo(' . Php::string($path) . "));\n"
            . "        \$profiles = [];\n"
            . $body
            . "\n"
            . "        return \$profiles === [] ? \$request : \$request->withProfiles(\$profiles);\n"
            . "    }\n";
    }

    private function collect(): string
    {
        $variable = '$' . $this->variable(true);
        $page = $this->surface->paginator->paginated() ? $this->pageType() : null;

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
            . '            ' . ($this->surface->menu() ? '($this->reader)' : $page . '::read') . "(\$document->pageMeta(), PageLinks::fromArray(\$document->links())),\n"
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

    /**
     * The page type a read is declared to return: the runtime page class, or a menu's `TPage`.
     */
    private function pageType(): string
    {
        return $this->surface->menu() ? 'TPage' : TypeSurface::pageClass($this->surface->paginator) ?? 'Page';
    }

    /**
     * Every runtime page class the query names: its paginator's, or, for a menu, the template's
     * bound and each pickable arm's.
     *
     * @return list<string>
     */
    private function pageClasses(): array
    {
        $s = $this->surface;
        if (!$s->menu()) {
            $page = TypeSurface::pageClass($s->paginator);

            return $page === null ? [] : [$page];
        }

        $classes = ['Page'];
        foreach ($s->pickers as $kind) {
            $classes[] = TypeSurface::pageClass($s->paginator->arms[$kind]) ?? 'Page';
        }

        return \array_values(\array_unique($classes));
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
    public static function constant(array $lines, string $type, string $name, string $value): string
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
    public static function list(array $values, string $name): string
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
    public static function union(array $values): string
    {
        $values = \array_values(\array_unique($values));
        \sort($values, \SORT_STRING);

        return \implode('|', \array_map(Php::docString(...), $values));
    }
}
