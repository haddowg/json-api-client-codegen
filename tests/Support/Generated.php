<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;

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

    /** @var array<string, string>|null */
    private static ?array $artefactFiles = null;

    public static function config(): GeneratorConfig
    {
        return new GeneratorConfig(self::NAMESPACE, 'MusicCatalog', PhpTarget::Php84);
    }

    /**
     * @return array<string, string> file contents keyed by path
     */
    public static function musicCatalog(): array
    {
        return self::$files ??= Generator::default()->generate(
            Fixtures::musicCatalogDescriptor(),
            self::config(),
            \hash_file('sha256', Fixtures::path('music-catalog.openapi.json')) ?: '',
        );
    }

    /**
     * A file generated from the document the reference artefact describes, which is what an
     * artefact parity check compares.
     */
    public static function artefactFile(string $path): string
    {
        $files = self::$artefactFiles ??= Generator::default()->generate(
            Fixtures::artefactDescriptor(),
            self::config(),
            \hash_file('sha256', Fixtures::path(Fixtures::ARTEFACT_DOCUMENT)) ?: '',
        );

        return $files[$path] ?? throw new \RuntimeException(\sprintf('The artefact document\'s output has no %s.', $path));
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
