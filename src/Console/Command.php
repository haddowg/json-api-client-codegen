<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Console;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Root\RuntimeCatalogueUnavailable;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Spec\UnsupportedContractException;

/**
 * `json-api-client-codegen <openapi-document> <output-directory> --namespace=…`
 *
 * Writes the generated client, or with `--check` writes nothing and exits 1 when the committed
 * output no longer matches what the document generates: the drift gate for CI.
 */
final class Command
{
    public const int OK = 0;

    public const int FAILURE = 1;

    public const int USAGE = 2;

    /** @var resource */
    private $stdout;

    /** @var resource */
    private $stderr;

    /**
     * @param resource|null $stdout
     * @param resource|null $stderr
     */
    public function __construct($stdout = null, $stderr = null, private readonly ?Generator $generator = null)
    {
        $this->stdout = $stdout ?? \STDOUT;
        $this->stderr = $stderr ?? \STDERR;
    }

    /**
     * @param list<string> $arguments the command-line arguments, without the binary
     */
    public function run(array $arguments): int
    {
        $positional = [];
        $options = [];
        foreach ($arguments as $argument) {
            if (\preg_match('/^--([a-z-]+)(?:=(.*))?$/', $argument, $m) === 1) {
                $options[$m[1]] = $m[2] ?? '';
            } else {
                $positional[] = $argument;
            }
        }

        if (isset($options['help']) || \count($positional) !== 2 || ($options['namespace'] ?? '') === '') {
            \fwrite(isset($options['help']) ? $this->stdout : $this->stderr, Usage::text());

            return isset($options['help']) ? self::OK : self::USAGE;
        }

        [$input, $output] = $positional;
        $namespace = \trim($options['namespace'], '\\');
        $segments = \explode('\\', $namespace);

        try {
            $config = new GeneratorConfig(
                $namespace,
                $options['client'] ?? \end($segments),
                isset($options['target']) ? (PhpTarget::tryFrom($options['target']) ?? throw new \InvalidArgumentException(\sprintf('--target must be 8.3 or 8.4, got "%s".', $options['target']))) : PhpTarget::running(),
            );

            $raw = @\file_get_contents($input);
            if ($raw === false) {
                throw SpecException::unreadable($input, 'the file could not be opened');
            }
            $document = SpecDocument::fromJson($raw, $input);

            $warning = ContractCompatibility::check($document->contract());
            if ($warning !== null) {
                \fwrite($this->stderr, 'Warning: ' . $warning . \PHP_EOL);
            }

            $generator = $this->generator ?? Generator::default();
            $files = $generator->generate(DescriptorBuilder::build($document), $config, \hash('sha256', $raw));
            foreach ($generator->warnings() as $generated) {
                \fwrite($this->stderr, 'Warning: ' . $generated . \PHP_EOL);
            }
        } catch (\InvalidArgumentException|SpecException|UnsupportedContractException|RuntimeCatalogueUnavailable $e) {
            \fwrite($this->stderr, $e->getMessage() . \PHP_EOL);

            return self::FAILURE;
        }

        return isset($options['check']) ? $this->check($files, $output) : $this->write($files, $output);
    }

    /**
     * @param array<string, string> $files
     */
    private function write(array $files, string $output): int
    {
        if (!\is_dir($output) && !@\mkdir($output, 0o775, true) && !\is_dir($output)) {
            \fwrite($this->stderr, \sprintf('Cannot create the output directory %s.', $output) . \PHP_EOL);

            return self::FAILURE;
        }

        foreach (self::generatedIn($output) as $stale) {
            if (!isset($files[$stale])) {
                \unlink($output . '/' . $stale);
            }
        }
        foreach ($files as $path => $contents) {
            \file_put_contents($output . '/' . $path, $contents);
        }

        \fwrite($this->stdout, \sprintf('Generated %d files in %s.', \count($files), $output) . \PHP_EOL);

        return self::OK;
    }

    /**
     * @param array<string, string> $files
     */
    private function check(array $files, string $output): int
    {
        $drift = [];
        foreach ($files as $path => $contents) {
            if (!\is_file($output . '/' . $path) || \file_get_contents($output . '/' . $path) !== $contents) {
                $drift[] = $path;
            }
        }
        foreach (self::generatedIn($output) as $existing) {
            if (!isset($files[$existing])) {
                $drift[] = $existing;
            }
        }

        if ($drift === []) {
            \fwrite($this->stdout, 'The generated client is up to date.' . \PHP_EOL);

            return self::OK;
        }

        \sort($drift, \SORT_STRING);
        \fwrite($this->stderr, 'The generated client is out of date. Regenerate it; these files differ:' . \PHP_EOL . '  ' . \implode(\PHP_EOL . '  ', $drift) . \PHP_EOL);

        return self::FAILURE;
    }

    /**
     * The files in `$output` this codegen wrote, so regeneration removes only its own.
     *
     * @return list<string>
     */
    private static function generatedIn(string $output): array
    {
        $found = [];
        foreach (\glob($output . '/*.php') ?: [] as $file) {
            $name = \basename($file);
            if ($name === FileKey::AUTOLOAD || \str_contains((string) \file_get_contents($file), '@generated')) {
                $found[] = $name;
            }
        }

        return $found;
    }
}
