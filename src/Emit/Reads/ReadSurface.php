<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\CountableDescriptor;
use haddowg\JsonApiCodegen\Descriptor\FilterDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\ProjectionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\Naming;

/**
 * Everything the read emitters need about one type, decided once.
 *
 * What exists is read from the descriptor and nothing else. The query builder exists when the
 * type has a collection read. The write projection exists when some single-resource operation
 * that returns this type (the fetch, the create, the update, or a custom action) declares
 * `include` or `fields`. The shared `withX()` trait exists when either of them projects.
 */
final class ReadSurface
{
    public readonly string $resource;

    public readonly string $base;

    public readonly string $file;

    public readonly ?OperationDescriptor $collection;

    public readonly bool $projection;

    /** @var list<string> the operations the write projection serves, as `METHOD path` */
    public readonly array $projectionSources;

    /** Whether the query builder's own operation declares `include` or `fields`. */
    public readonly bool $queryProjects;

    /** @var list<string> every include path any projecting operation accepts, first seen first */
    public readonly array $includable;

    /** @var list<string> relations a `withX()` is generated for: depth-1 includable paths */
    public readonly array $narrowing;

    /** @var list<string>|null the type's own sparse fieldset members, null when no operation declares `fields[type]` */
    public readonly ?array $fields;

    /** @var list<FilterSlot> */
    public readonly array $filters;

    /** @var list<ShapeAlias> */
    public readonly array $shapes;

    /** @var list<string> */
    public readonly array $sortable;

    public readonly ?CountableDescriptor $countable;

    public readonly PaginatorDescriptor $paginator;

    /** @var array<string, string> picker method => the menu arm's kind, in declaration order; empty unless the collection pages by a menu */
    public readonly array $pickers;

    /** @var list<string> build-time warnings: filter method and picker collisions */
    public readonly array $warnings;

    /** @var list<RelationDescriptor> relations the query's `withX()` can shape with a Relationship Query */
    public readonly array $queryRelating;

    /** @var list<RelationDescriptor> relations the single read's `withX()` can shape with a Relationship Query */
    public readonly array $readRelating;

    /** The Relationship Queries profile URI, when either read can shape a relation. */
    public readonly ?string $relatedQueryProfile;

    public function __construct(ApiDescriptor $api, public readonly ResourceDescriptor $descriptor)
    {
        $type = $descriptor->type;
        $this->resource = Naming::resource($type);
        $this->base = Naming::base($type);
        $this->file = FileKey::resource($type);
        $this->collection = $descriptor->operation(OperationKind::FetchMany);
        $this->queryProjects = $this->collection?->projection !== null;

        $projections = [];
        $sources = [];
        foreach ([OperationKind::FetchOne, OperationKind::Create, OperationKind::Update] as $kind) {
            $operation = $descriptor->operation($kind);
            if ($operation?->projection !== null) {
                $projections[] = $operation->projection;
                $sources[] = $operation->method . ' ' . $operation->path;
            }
        }
        foreach ($api->resources as $owner) {
            foreach ($owner->actions as $action) {
                if ($action->outputType === $type && $action->projection !== null) {
                    $projections[] = $action->projection;
                    $sources[] = $action->method . ' ' . $action->path;
                }
            }
        }
        $this->projectionSources = $sources;
        $this->projection = $projections !== [];

        $queryProjection = $this->collection?->projection;
        $all = $queryProjection === null ? $projections : [$queryProjection, ...$projections];

        $this->includable = self::includable($all);
        $this->narrowing = \array_values(\array_filter(
            $this->includable,
            static fn(string $path): bool => !\str_contains($path, '.') && $descriptor->relation($path) !== null,
        ));
        $this->fields = self::fieldset($all, $type);

        $this->sortable = $this->collection === null ? [] : $descriptor->sortable;
        $this->countable = $this->collection === null || $descriptor->countable?->tokens === [] ? null : $descriptor->countable;
        $this->paginator = $this->collection === null ? PaginatorDescriptor::none() : $descriptor->paginator;
        [$this->pickers, $pickerWarnings] = self::pickers($this->paginator, $this->collection === null ? $type : $this->collection->method . ' ' . $this->collection->path);

        [$this->filters, $this->shapes, $filterWarnings] = $this->collection === null
            ? [[], [], []]
            : self::filters($descriptor, self::declaresDateInput($descriptor));
        $this->warnings = [...$filterWarnings, ...$pickerWarnings];

        $fetchOne = $descriptor->operation(OperationKind::FetchOne)?->projection;
        $this->queryRelating = self::relatingIn($descriptor, $queryProjection);
        $this->readRelating = self::relatingIn($descriptor, $fetchOne);
        $this->relatedQueryProfile = $this->queryRelating !== [] ? $queryProjection?->relatedQuery : ($this->readRelating !== [] ? $fetchOne?->relatedQuery : null);
    }

    /** Whether the single read gets its own projection, carrying the Relationship Query closures. */
    public function readProjection(): bool
    {
        return $this->readRelating !== [];
    }

    /**
     * The depth-1 includes of an operation that declares `relatedQuery` whose relation can be shaped.
     *
     * @return list<RelationDescriptor>
     */
    private static function relatingIn(ResourceDescriptor $descriptor, ?ProjectionDescriptor $projection): array
    {
        if ($projection?->relatedQuery === null) {
            return [];
        }

        $out = [];
        foreach ($projection->includable as $path) {
            $relation = \str_contains($path, '.') ? null : $descriptor->relation($path);
            if ($relation !== null && RelatedQuerySource::applies($relation)) {
                $out[] = $relation;
            }
        }

        return $out;
    }

    /** Whether the collection pages by a menu the caller picks a strategy from. */
    public function menu(): bool
    {
        return $this->paginator->kind === PaginatorKind::Multi;
    }

    /**
     * One `pageBy<Kind>()` per arm of a menu. A kind is server-chosen, so two that normalise to
     * one method name (`by-date` and `by_date`) cannot both be generated: the later arm is left to
     * `pageRaw()`, with a warning.
     *
     * @return array{array<string, string>, list<string>}
     */
    private static function pickers(PaginatorDescriptor $paginator, string $label): array
    {
        $pickers = [];
        $warnings = [];

        foreach (\array_keys($paginator->arms) as $kind) {
            $method = 'pageBy' . Naming::pascal($kind);
            if ($method === 'pageBy' || isset($pickers[$method])) {
                $warnings[] = \sprintf('the `%s` strategy of `%s` has no picker of its own, because its kind gives the method name `%s()` %s. Select it through pageRaw().', $kind, $label, $method, $method === 'pageBy' ? 'and no more' : 'that another strategy already has');

                continue;
            }
            $pickers[$method] = $kind;
        }

        return [$pickers, $warnings];
    }

    /** Whether the projecting trait has anything to carry. */
    public function trait(): bool
    {
        return ($this->projection || $this->queryProjects && !$this->menu()) && $this->projects();
    }

    /**
     * Whether there are `withX()` or `fields()` methods to generate at all. A menu query writes
     * them inline rather than through the trait, because its second template is one the trait's
     * `static<TProj&Marker>` cannot carry.
     */
    public function projects(): bool
    {
        return $this->narrowing !== [] || $this->fields !== null;
    }

    public function name(string $suffix): string
    {
        return Naming::perType($this->descriptor->type, $suffix);
    }

    public function alias(string $suffix): string
    {
        return Naming::alias($this->descriptor->type, $suffix);
    }

    /** Whether any filter is structured, so the nested-member guard has members to check. */
    public function structuredFilters(): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->shape !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * The resource's date input alias is declared by the write emitters, for a type with a date
     * member in either write shape. A date filter bound names it where it exists and spells out
     * the union it stands for where it does not.
     */
    private static function declaresDateInput(ResourceDescriptor $descriptor): bool
    {
        foreach ([...$descriptor->createAttributes, ...$descriptor->updateAttributes] as $attribute) {
            if (\in_array($attribute->format, ['date', 'date-time', 'time'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<ProjectionDescriptor> $projections
     *
     * @return list<string>
     */
    private static function includable(array $projections): array
    {
        $paths = [];
        foreach ($projections as $projection) {
            foreach ($projection->includable as $path) {
                $paths[$path] = true;
            }
        }

        return \array_keys($paths);
    }

    /**
     * @param list<ProjectionDescriptor> $projections
     *
     * @return list<string>|null
     */
    private static function fieldset(array $projections, string $type): ?array
    {
        $fields = null;
        foreach ($projections as $projection) {
            foreach ($projection->fieldsets[$type] ?? [] as $field) {
                $fields ??= [];
                if (!\in_array($field, $fields, true)) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }

    /**
     * Resolve each filter's value type, its shape alias, and its method name.
     *
     * Two filters can normalise to one method (`artist.name` and `artistName` both give
     * `whereArtistName`). A filter whose name is already a bare identifier keeps the plain name,
     * since that is the one a reader would guess; the others spell their separators out
     * (`whereArtistDotName`). A fallback that still collides gets no method at all, with a
     * warning, and stays reachable through `filter()`.
     *
     * @return array{list<FilterSlot>, list<ShapeAlias>, list<string>}
     */
    private static function filters(ResourceDescriptor $descriptor, bool $dateInput): array
    {
        return self::filterSlots($descriptor->type, '', $descriptor->filterable, $dateInput, $descriptor->type);
    }

    /**
     * The filter slots for one vocabulary of filters: a collection's own, or a relation's, whose
     * aliases take the relation's name after the type's (`AlbumTracksRangeShape`).
     *
     * @param array<string, FilterDescriptor> $filterable
     *
     * @return array{list<FilterSlot>, list<ShapeAlias>, list<string>}
     */
    public static function filterSlots(string $type, string $infix, array $filterable, bool $dateInput, string $label): array
    {
        $dateAlias = Naming::alias($type, 'DateInput');
        $types = new FilterTypes($dateInput ? $dateAlias : '\DateTimeImmutable|string', $dateInput);

        $shapes = [];
        $slots = [];
        foreach ($filterable as $filter) {
            $schema = $filter->schema;
            $shape = null;
            if ($schema !== null && FilterTypes::structured($schema)) {
                [$doc, $aliases] = $types->shape($schema);
                $shape = self::shapeAlias($shapes, $type, $infix, $filter->name, $doc, FilterTypes::members($schema), FilterTypes::range($schema), FilterTypes::dateRange($schema), $aliases);
                $value = new ValueType($shape->name, 'array', [$shape->name]);
            } else {
                $value = $types->type($schema);
            }
            $slots[] = ['name' => $filter->name, 'type' => $value, 'shape' => $shape];
        }

        [$methods, $warnings] = self::methods($label, \array_map(static fn(array $slot): string => $slot['name'], $slots));

        $out = [];
        foreach ($slots as $slot) {
            $out[] = new FilterSlot($slot['name'], $methods[$slot['name']], $slot['type'], $slot['shape']);
        }

        return [$out, \array_values($shapes), $warnings];
    }

    /**
     * Find or declare the alias for one structured value. A shape seen before reuses its alias,
     * so two numeric ranges share one `RangeShape`.
     *
     * @param array<string, ShapeAlias> $shapes declared so far, keyed by shape
     * @param list<string>              $members
     * @param list<string>              $aliases
     */
    private static function shapeAlias(array &$shapes, string $type, string $infix, string $filter, string $doc, array $members, bool $range, bool $dateRange, array $aliases): ShapeAlias
    {
        if (isset($shapes[$doc])) {
            return $shapes[$doc];
        }

        $taken = \array_map(static fn(ShapeAlias $alias): string => $alias->name, $shapes);
        $name = match (true) {
            $dateRange => Naming::alias($type, $infix . 'DateRangeShape'),
            $range => Naming::alias($type, $infix . 'RangeShape'),
            default => Naming::alias($type, $infix . Naming::pascal($filter) . 'FilterShape'),
        };
        if (\in_array($name, $taken, true)) {
            $name = Naming::alias($type, $infix . Naming::pascal($filter) . 'FilterShape');
        }

        return $shapes[$doc] = new ShapeAlias($name, $doc, $members, $range, $aliases);
    }

    /**
     * @param list<string> $filters
     *
     * @return array{array<string, string|null>, list<string>}
     */
    private static function methods(string $type, array $filters): array
    {
        $groups = [];
        foreach ($filters as $filter) {
            $groups[\strtolower(Naming::filterMethod($filter))][] = $filter;
        }

        $methods = [];
        $warnings = [];
        foreach ($groups as $group) {
            if (\count($group) === 1) {
                $methods[$group[0]] = Naming::filterMethod($group[0]);
                continue;
            }

            $bare = \array_values(\array_filter($group, static fn(string $filter): bool => \preg_match('/^[A-Za-z0-9]+$/', $filter) === 1));
            foreach ($group as $filter) {
                $methods[$filter] = \count($bare) === 1 && $bare[0] === $filter
                    ? Naming::filterMethod($filter)
                    : 'where' . self::spelled($filter);
            }
            $warnings[] = \sprintf(
                'The `%s` filters %s all normalise to %s(); %s.',
                $type,
                \implode(', ', \array_map(static fn(string $filter): string => '`' . $filter . '`', $group)),
                Naming::filterMethod($group[0]),
                \implode(', ', \array_map(static fn(string $filter): string => '`' . $filter . '` is ' . $methods[$filter] . '()', $group)),
            );
        }

        $claims = [];
        foreach ($methods as $filter => $method) {
            $claims[\strtolower($method)][] = (string) $filter;
        }
        foreach ($claims as $claimants) {
            if (\count($claimants) < 2) {
                continue;
            }
            // A fallback never takes a name from a filter it is natural for.
            $natural = \array_values(\array_filter($claimants, static fn(string $filter): bool => $methods[$filter] === Naming::filterMethod($filter)));
            $losers = \count($natural) === 1 ? \array_values(\array_diff($claimants, $natural)) : $claimants;
            foreach ($losers as $filter) {
                $methods[$filter] = null;
            }
            $warnings[] = \sprintf(
                'The `%s` filters %s have no distinct method; use filter() for them.',
                $type,
                \implode(', ', \array_map(static fn(string $filter): string => '`' . $filter . '`', $losers)),
            );
        }

        return [$methods, $warnings];
    }

    /** `artist.name` → `ArtistDotName`: the separators a plain PascalCase drops, spelled as words. */
    private static function spelled(string $filter): string
    {
        $words = ['.' => 'Dot', '-' => 'Dash', '_' => 'Underscore'];
        $out = '';
        foreach (\preg_split('/([^A-Za-z0-9])/', $filter, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
            $out .= \preg_match('/^[A-Za-z0-9]+$/', $part) === 1 ? \ucfirst($part) : ($words[$part] ?? 'X');
        }

        return $out;
    }
}
