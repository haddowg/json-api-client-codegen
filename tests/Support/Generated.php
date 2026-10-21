<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

use haddowg\JsonApiCodegen\Emit\Enums\EnumEmitter;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Writes\WritesEmitter;

/**
 * The music-catalog fixture's generated output, built once per process.
 *
 * It is generated into the artefact's own namespace on the 8.4 target, so a generated
 * declaration lines up with its hand-written counterpart in `tests/fixtures/artefact` and the
 * golden output in `tests/golden/generated` can be analysed beside it.
 */
final class Generated
{
    public const string NAMESPACE = 'haddowg\\JsonApiClient\\Tests\\Generated\\MusicCatalog';

    /** @var array<string, string>|null */
    private static ?array $files = null;

    /**
     * Every shipped emitter, plus a stand-in for the read model this branch does not have yet:
     * the write emitter contributes to the resource class, and a contribution with no target is
     * rejected.
     */
    public static function generator(): Generator
    {
        return new Generator([new EnumEmitter(), new WritesEmitter(), new ReadModelStandIn()]);
    }

    public static function config(): GeneratorConfig
    {
        return new GeneratorConfig(self::NAMESPACE, 'MusicCatalog', PhpTarget::Php84);
    }

    /**
     * @return array<string, string> file contents keyed by path
     */
    public static function musicCatalog(): array
    {
        return self::$files ??= self::generator()->generate(
            Fixtures::musicCatalogDescriptor(),
            self::config(),
            \hash_file('sha256', Fixtures::path('music-catalog.openapi.json')) ?: '',
        );
    }

    public static function file(string $path): string
    {
        $files = self::musicCatalog();

        if (!isset($files[$path])) {
            throw new \RuntimeException(\sprintf('The music-catalog output has no %s.', $path));
        }

        return $files[$path];
    }
}
