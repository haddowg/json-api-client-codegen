<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * A Relationship Query: how a to-many comes back, filtered and sorted by the vocabulary its own
 * related read declares (`AlbumTracksRelatedQuery`). An included relation sends it under the
 * Relationship Queries profile; the relation's `related()` sends it as plain `filter` and `sort`.
 *
 * The class carries that vocabulary itself, as its constants and class-local aliases, because it is
 * the relation's rather than the parent type's or the related type's: a relation's endpoint may
 * filter by less than the related collection does.
 */
final class RelatedQuerySource
{
    private const string RUNTIME = 'haddowg\\JsonApiClient\\';

    /**
     * Whether a relation can be shaped from a primary read: a to-many whose related read declares
     * something to filter or sort by. A to-one has nothing to order, and the profile refuses one.
     */
    public static function applies(RelationDescriptor $relation): bool
    {
        return $relation->cardinality === Cardinality::Many
            && $relation->relatedRead !== null
            && ($relation->filterable !== [] || $relation->sortable !== []);
    }

    public static function name(string $type, string $relation): string
    {
        return Naming::perType($type, Naming::pascal($relation) . 'RelatedQuery');
    }

    /**
     * The `withX()` that includes the relation and takes the optional closure shaping it, as a
     * query or read projection re-declares it over the trait's plain one.
     */
    public static function withMethod(string $type, string $relation, string $value, string $template): string
    {
        $query = self::name($type, $relation);

        return Php::docblock([
            ...Php::wrap(\sprintf('Include the `%s` relation, narrowing the projection so `%s->%s` type-checks, and optionally shape how it comes back: the closure filters and sorts it under the Relationship Queries profile.', $relation, $value, Naming::member($relation)), 4),
            '',
            '@param (callable(' . $query . '): ' . $query . ')|null $query',
            '',
            '@return static<TProj&' . Naming::marker($type, $relation) . $template . '>',
        ], 4)
            . '    public function ' . Naming::includeMethod($relation) . "(?callable \$query = null): static\n"
            . "    {\n"
            . '        return $this->relating(' . Php::string($relation) . ', new ' . $query . "(), \$query);\n"
            . "    }\n";
    }

    /**
     * @return array{ClassUnit, list<string>} the class and any build-time warnings
     */
    public static function unit(string $type, RelationDescriptor $relation, string $file): array
    {
        $name = self::name($type, $relation->name);
        $infix = Naming::pascal($relation->name);
        $label = $type . '.' . $relation->name;
        [$filters, $shapes, $warnings] = ReadSurface::filterSlots($type, $infix, $relation->filterable, false, $label);
        $structured = \array_filter($filters, static fn(FilterSlot $filter): bool => $filter->shape !== null) !== [];
        $filterShape = Naming::alias($type, $infix . 'FilterShape');
        $sortToken = Naming::alias($type, $infix . 'SortToken');
        $endpoint = $relation->relatedRead === null ? '' : $relation->relatedRead->method . ' ' . $relation->relatedRead->path;

        $doc = [
            ...Php::wrap(\sprintf('How the `%s` of %s `%s` resource come back: filtered and sorted by the vocabulary `%s` declares. A read\'s `%s()` sends it under the Relationship Queries profile, and the relation\'s `related()` as that endpoint\'s own `filter` and `sort`.', $relation->name, \in_array(\strtolower($type[0]), ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a', $type, $endpoint, Naming::includeMethod($relation->name))),
            '',
        ];
        foreach ($shapes as $shape) {
            $doc[] = '@phpstan-type ' . $shape->name . ' ' . $shape->shape;
        }
        if ($filters !== []) {
            $doc[] = '@phpstan-type ' . $filterShape . ' array{' . \implode(', ', \array_map(static fn(FilterSlot $filter): string => FilterTypes::key($filter->name) . '?: ' . $filter->type->doc, $filters)) . '}';
        }
        if ($relation->sortable !== []) {
            $doc[] = '@phpstan-type ' . $sortToken . ' ' . ReadSources::union($relation->sortable);
        }
        $doc[] = '';
        $doc[] = '@generated from the `filter` and `sort` parameters of `' . $endpoint . '`';

        $members = [];
        $imports = [self::RUNTIME . 'Query\\RelatedQuery'];
        if ($filters !== []) {
            $members[] = ReadSources::constant(
                ['The filters the relation accepts. The runtime guard on both array doors checks names against this.', '', '@var list<string>'],
                'array',
                'FILTERS',
                ReadSources::list(\array_map(static fn(FilterSlot $filter): string => $filter->name, $filters), 'FILTERS'),
            );
            if ($structured) {
                $map = [];
                foreach ($filters as $filter) {
                    if ($filter->shape !== null) {
                        $map[] = '        ' . Php::string($filter->name) . ' => [' . \implode(', ', \array_map(Php::string(...), $filter->shape->members)) . "],\n";
                    }
                }
                $members[] = ReadSources::constant(
                    ['The members a structured filter\'s own value accepts.', '', '@var array<string, list<string>>'],
                    'array',
                    'FILTER_MEMBERS',
                    "[\n" . \implode('', $map) . '    ]',
                );
            }
        }
        if ($relation->sortable !== []) {
            $members[] = ReadSources::constant(
                ['The signed sort tokens the relation accepts.', '', '@var list<string>'],
                'array',
                'SORT_TOKENS',
                ReadSources::list($relation->sortable, 'SORT_TOKENS'),
            );
        }
        foreach ($filters as $filter) {
            if ($filter->method !== null) {
                $members[] = ReadSources::where($filter);
            }
        }
        if ($filters !== []) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownFilterException';
            $members[] = ReadSources::filterDoors($filterShape);
        }
        if ($relation->sortable !== []) {
            $imports[] = self::RUNTIME . 'Exceptions\\UnknownSortException';
            $members[] = ReadSources::sortDoors($sortToken, 'self');
        }
        if ($filters !== []) {
            $members[] = ReadSources::guardFilters($filterShape, 'self', $structured);
        }

        return [
            new ClassUnit(
                $name,
                UnitKind::FinalClass,
                $file,
                Php::docblock($doc, 0, false) . 'final class ' . $name . " extends RelatedQuery\n{\n" . \implode("\n", $members) . "}\n",
                $imports,
            ),
            $warnings,
        ];
    }
}
