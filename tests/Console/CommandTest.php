<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Console;

use haddowg\JsonApiCodegen\Console\Command;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Command::class)]
final class CommandTest extends TestCase
{
    private string $output;

    /** @var resource */
    private $stdout;

    /** @var resource */
    private $stderr;

    protected function setUp(): void
    {
        $this->output = \sys_get_temp_dir() . '/json-api-codegen-' . \bin2hex(\random_bytes(6));
        $this->stdout = \fopen('php://memory', 'w+') ?: throw new \RuntimeException('no stream');
        $this->stderr = \fopen('php://memory', 'w+') ?: throw new \RuntimeException('no stream');
    }

    protected function tearDown(): void
    {
        foreach (\glob($this->output . '/*') ?: [] as $file) {
            \unlink($file);
        }
        if (\is_dir($this->output)) {
            \rmdir($this->output);
        }
    }

    public function testItWritesTheGeneratedClientAndItsClassMap(): void
    {
        self::assertSame(Command::OK, $this->invoke(...$this->arguments()));

        self::assertFileExists($this->output . '/autoload.php');
        self::assertFileExists($this->output . '/enums.php');
        self::assertStringContainsString('namespace App\\Api\\MusicCatalog;', (string) \file_get_contents($this->output . '/enums.php'));
        self::assertStringContainsString('Generated ', $this->read($this->stdout));
    }

    public function testCheckPassesOnFreshOutputAndFailsOnDrift(): void
    {
        $this->invoke(...$this->arguments());

        self::assertSame(Command::OK, $this->invoke(...$this->arguments(), ...['--check']));

        \file_put_contents($this->output . '/enums.php', "<?php\n// @generated but edited\n");
        self::assertSame(Command::FAILURE, $this->invoke(...$this->arguments(), ...['--check']));
        self::assertStringContainsString('enums.php', $this->read($this->stderr));
    }

    public function testCheckWritesNothing(): void
    {
        self::assertSame(Command::FAILURE, $this->invoke(...$this->arguments(), ...['--check']));
        self::assertDirectoryDoesNotExist($this->output);
    }

    public function testRegeneratingRemovesStaleGeneratedFilesButNothingElse(): void
    {
        $this->invoke(...$this->arguments());
        \file_put_contents($this->output . '/resource-gone.php', "<?php\n/** @generated */\n");
        \file_put_contents($this->output . '/Handwritten.php', "<?php\n");

        $this->invoke(...$this->arguments());

        self::assertFileDoesNotExist($this->output . '/resource-gone.php');
        self::assertFileExists($this->output . '/Handwritten.php');
    }

    public function testTheClientNameDefaultsToTheLastNamespaceSegment(): void
    {
        $this->invoke(...$this->arguments());

        self::assertStringContainsString('the generated MusicCatalog client', (string) \file_get_contents($this->output . '/autoload.php'));
    }

    public function testAMissingNamespaceIsAUsageError(): void
    {
        self::assertSame(Command::USAGE, $this->invoke(Fixtures::path('music-catalog.openapi.json'), $this->output));
        self::assertStringContainsString('--namespace', $this->read($this->stderr));
    }

    public function testAnUnknownTargetIsRejected(): void
    {
        self::assertSame(Command::FAILURE, $this->invoke(...$this->arguments(), ...['--target=7.4']));
        self::assertStringContainsString('--target must be 8.3 or 8.4', $this->read($this->stderr));
    }

    public function testAnUnreadableDocumentIsReportedNotThrown(): void
    {
        self::assertSame(Command::FAILURE, $this->invoke('/nope/missing.json', $this->output, '--namespace=App\\Api'));
        self::assertStringContainsString('missing.json', $this->read($this->stderr));
    }

    /**
     * @return list<string>
     */
    private function arguments(): array
    {
        return [Fixtures::path('music-catalog.openapi.json'), $this->output, '--namespace=App\\Api\\MusicCatalog', '--target=8.4'];
    }

    private function invoke(string ...$arguments): int
    {
        return (new Command($this->stdout, $this->stderr))->run(\array_values($arguments));
    }

    /**
     * @param resource $stream
     */
    private function read($stream): string
    {
        \rewind($stream);

        return (string) \stream_get_contents($stream);
    }
}
