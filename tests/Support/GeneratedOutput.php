<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Spec\SpecDocument;

/**
 * The generated output the suite runs and analyses, one directory per suite.
 *
 * Nothing here is committed. Each suite is generated from its document on demand: the behaviour
 * tests load it from `build/generated-output/<suite>`, `composer generate-output` writes every
 * suite there for `composer phpstan-generated` and `composer cs-check-generated`, and CI writes
 * the base's and the head's to diff them for review (see ADR 0015).
 *
 * - `generated`: the music catalog on the 8.4 target, in the reference artefact's namespace.
 * - `paginators`: the hand-built `paginators.openapi.json`, one collection per strategy.
 * - `witness`: core's contract witness on the 8.4 target.
 * - `form-fields`: the witness with `bulk-import`'s form bodies given fields.
 * - `unions`: the witness on the 8.3 target, so its reads run on any supported PHP.
 *
 * Written out, the suites sit beside the music catalog's serialised descriptor.
 */
final class GeneratedOutput
{
    public const string DIRECTORY = __DIR__ . '/../../build/generated-output';

    public const array SUITES = ['generated', 'paginators', 'witness', 'form-fields', 'unions'];

    /** @var array<string, array{array<string, string>, list<string>}> */
    private static array $generated = [];

    /** @var array<string, true> */
    private static array $written = [];

    /**
     * A suite's files keyed by path, generated once per process.
     *
     * @return array<string, string>
     */
    public static function files(string $suite): array
    {
        return (self::$generated[$suite] ??= self::generate($suite))[0];
    }

    /**
     * The warnings generating a suite reported.
     *
     * @return list<string>
     */
    public static function warnings(string $suite): array
    {
        return (self::$generated[$suite] ??= self::generate($suite))[1];
    }

    /**
     * Generates a suite afresh, with nothing cached, and returns its files and warnings.
     *
     * @return array{array<string, string>, list<string>}
     */
    public static function generate(string $suite): array
    {
        [$json, $config, $hash] = match ($suite) {
            'generated' => [self::read(Fixtures::musicCatalogPath()), Generated::config(), self::fileHash(Fixtures::musicCatalogPath())],
            'paginators' => [self::read(Fixtures::path('paginators.openapi.json')), self::config($suite), self::fileHash(Fixtures::path('paginators.openapi.json'))],
            'witness' => [Fixtures::witnessJson(), self::config($suite), self::fileHash(Fixtures::witnessPath())],
            'form-fields' => [$formFields = self::formFieldsJson(), self::config($suite), \hash('sha256', $formFields)],
            'unions' => [$unions = self::unionsJson(), self::config($suite), \hash('sha256', $unions)],
            default => throw new \InvalidArgumentException('No generated-output suite named ' . $suite . '.'),
        };

        $generator = Generator::default();
        $files = $generator->generate(DescriptorBuilder::build(SpecDocument::fromJson($json, 'the ' . $suite . ' document')), $config, $hash);

        return [$files, $generator->warnings()];
    }

    public static function config(string $suite): GeneratorConfig
    {
        return match ($suite) {
            'generated' => Generated::config(),
            'paginators' => new GeneratorConfig('haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators', 'Paginators', PhpTarget::Php84),
            'witness' => new GeneratorConfig('haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness', 'Witness', PhpTarget::Php84),
            'form-fields' => new GeneratorConfig('haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields', 'FormFields', PhpTarget::Php84),
            'unions' => new GeneratorConfig('haddowg\\JsonApiCodegen\\Tests\\Golden\\Unions', 'Unions', PhpTarget::Php83),
            default => throw new \InvalidArgumentException('No generated-output suite named ' . $suite . '.'),
        };
    }

    /**
     * Writes a suite's output under the build directory, once per process, and loads it through
     * its generated autoloader.
     */
    public static function load(string $suite): void
    {
        if (!isset(self::$written[$suite])) {
            self::writeSuite($suite, self::DIRECTORY);
            self::$written[$suite] = true;
        }

        require_once self::DIRECTORY . '/' . $suite . '/autoload.php';
    }

    /**
     * Writes every suite into `<directory>/<suite>`, replacing what was there, beside
     * `music-catalog.descriptor.json`: what the codegen read from the music catalog, serialised
     * deterministically, so a diff of it is a change in what the codegen understood.
     */
    public static function write(string $directory): void
    {
        foreach (self::SUITES as $suite) {
            self::writeSuite($suite, $directory);
        }

        $descriptor = DescriptorSerializer::toJson(Fixtures::musicCatalogDescriptor());
        if (\file_put_contents($directory . '/music-catalog.descriptor.json', $descriptor) === false) {
            throw new \RuntimeException('Could not write ' . $directory . '/music-catalog.descriptor.json.');
        }
    }

    /** The witness, re-encoded compactly. */
    public static function unionsJson(): string
    {
        $document = \json_decode(Fixtures::witnessJson(), true, flags: \JSON_THROW_ON_ERROR);

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * The witness with bulk-import's form bodies given fields: core describes its multipart body
     * as a bare object, and a form body is narrowed only once its schema lists `properties`.
     */
    public static function formFieldsJson(): string
    {
        $document = \json_decode(Fixtures::witnessJson(), true, flags: \JSON_THROW_ON_ERROR);

        $content = &$document;
        foreach (['paths', '/articles/-actions/bulk-import', 'post', 'requestBody', 'content'] as $key) {
            if (!\is_array($content) || !\array_key_exists($key, $content)) {
                throw new \RuntimeException('The witness has no bulk-import form bodies at ' . $key . '.');
            }
            $content = &$content[$key];
        }
        if (!\is_array($content) || !\array_key_exists('multipart/form-data', $content)) {
            throw new \RuntimeException('The witness\'s bulk-import takes no multipart body.');
        }
        $content['multipart/form-data'] = ['schema' => [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'cover' => ['type' => 'string', 'format' => 'binary'],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                'rows' => ['type' => 'integer'],
                'status' => ['type' => 'string', 'enum' => ['draft', 'published']],
            ],
            'required' => ['title'],
        ]];
        $content['application/x-www-form-urlencoded'] = ['schema' => [
            'type' => 'object',
            'properties' => ['title' => ['type' => 'string']],
            'required' => ['title'],
            'additionalProperties' => ['type' => 'integer'],
        ]];
        unset($content);

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    private static function writeSuite(string $suite, string $directory): void
    {
        $target = $directory . '/' . $suite;
        if (!\is_dir($target) && !\mkdir($target, 0o775, true) && !\is_dir($target)) {
            throw new \RuntimeException('Could not create ' . $target . '.');
        }
        foreach (\glob($target . '/*.php') ?: [] as $stale) {
            \unlink($stale);
        }
        foreach (self::files($suite) as $path => $contents) {
            if (\file_put_contents($target . '/' . $path, $contents) === false) {
                throw new \RuntimeException('Could not write ' . $target . '/' . $path . '.');
            }
        }
    }

    private static function read(string $path): string
    {
        $json = \file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException('Could not read ' . $path . '.');
        }

        return $json;
    }

    private static function fileHash(string $path): string
    {
        return \hash_file('sha256', $path) ?: '';
    }
}
