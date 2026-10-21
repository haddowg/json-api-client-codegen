<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;

/**
 * The read side of each type: the shared `withX()` trait, the write-response projection, the
 * collection query builder, and the filter, sort, include, field and count aliases and
 * constants they are checked against.
 *
 * Each is generated only where the descriptor permits it. A type with no collection read has no
 * query builder; one whose single-resource operations declare neither `include` nor `fields` has
 * no projection; a query builder has `sort()` only where the endpoint sorts, `withCount()` only
 * where it counts, and the pagination methods of its own paginator and no other.
 */
final class ReadsEmitter implements Emitter
{
    /** The read emitter's place among the contributions to a resource class. */
    public const int ORDER = 30;

    /** @var list<string> */
    private array $warnings = [];

    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        $this->warnings = [];

        foreach ($api->resources as $resource) {
            $surface = new ReadSurface($api, $resource);
            \array_push($this->warnings, ...$surface->warnings);
            $sources = new ReadSources($surface);

            if ($surface->trait()) {
                yield $sources->projecting();
            }
            if ($surface->projection) {
                yield $sources->projection();
            }
            if ($surface->collection !== null) {
                yield $sources->query();
            }

            $contribution = $sources->contribution();
            if ($contribution !== null) {
                yield $contribution;
            }
        }
    }

    /**
     * What the last run found worth telling the user: filters whose method names collide.
     *
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
