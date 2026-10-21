<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;

/**
 * Runs every emitter over one descriptor and assembles the result into files.
 */
final class Generator
{
    /** @var list<string> */
    private array $warnings = [];

    /**
     * @param list<Emitter> $emitters
     */
    public function __construct(private readonly array $emitters) {}

    /** Every emitter this codegen ships, in no particular order: the assembler orders output. */
    public static function default(): self
    {
        return new self([
            new Enums\EnumEmitter(),
            new ReadModel\ReadModelEmitter(),
            new Writes\WritesEmitter(),
            new Reads\ReadsEmitter(),
            new Surface\SurfaceEmitter(),
            new Root\ClientEmitter(),
            new Root\ErrorCodeEmitter(),
        ]);
    }

    /**
     * @return array<string, string> file contents keyed by path relative to the output directory
     */
    public function generate(ApiDescriptor $api, GeneratorConfig $config, string $specHash): array
    {
        $units = [];
        $this->warnings = [];
        foreach ($this->emitters as $emitter) {
            foreach ($emitter->emit($api, $config) as $unit) {
                $units[] = $unit;
            }
            if ($emitter instanceof Warns) {
                \array_push($this->warnings, ...$emitter->warnings());
            }
        }

        return Assembler::assemble($units, $config, $specHash);
    }

    /**
     * What the last {@see generate()} run had to tell the user, in emitter order.
     *
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
