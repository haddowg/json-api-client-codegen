<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Assembler;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Identifier;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Strings from the document reach generated PHP as data, never as code: a name that is not a PHP
 * identifier falls back or is refused, and a value inside a docblock cannot close it.
 *
 * Each document below is the music-catalog fixture with strings swapped for ones that would run
 * code at load time if they were written into the output verbatim. Every one defines a constant
 * whose name starts with `MARKER_`, and the varname also touches a file, so the test can tell.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(Generator::class)]
#[CoversClass(Assembler::class)]
#[CoversClass(Identifier::class)]
#[CoversClass(Naming::class)]
#[CoversClass(Php::class)]
final class DocumentStringsTest extends TestCase
{
    private const string NAMESPACE = 'Generated\\DocumentStrings';

    private const string VARNAME = "Upcoming = 'upcoming'; } \\define(\"MARKER_VARNAME\", 1); \\touch(__DIR__ . \"/marker-touched\"); enum MarkerVarname: string { case Upcoming";

    private const string ENUM_VALUE = 'x*/ define("MARKER_ENUM_VALUE", 1); /*';

    private const string NEWLINE_VALUE = "y\n*/ define(\"MARKER_NEWLINE_VALUE\", 1); /*";

    private const string DISCRIMINATOR = 'vinyl*/ define("MARKER_DISCRIMINATOR", 1); /*';

    private const string MEMBER = 'rpm*/ define("MARKER_MEMBER", 1); /*';

    private const string PROSE = "Prose.\n*/ define(\"MARKER_PROSE\", 1); /*";

    private string $directory;

    protected function setUp(): void
    {
        $this->directory = \sys_get_temp_dir() . '/codegen-document-strings-' . \bin2hex(\random_bytes(6));
        \mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach (\glob($this->directory . '/*') ?: [] as $file) {
            \unlink($file);
        }
        \rmdir($this->directory);
    }

    public function testGeneratedFilesParseAndRunNothingFromTheDocument(): void
    {
        $generator = Generator::default();
        $files = $generator->generate(
            DescriptorBuilder::build(self::document()),
            new GeneratorConfig(self::NAMESPACE, 'Strings', PhpTarget::running()),
            'test',
        );
        foreach ($files as $path => $contents) {
            \file_put_contents($this->directory . '/' . $path, $contents);
        }

        foreach (\array_keys($files) as $path) {
            [$output, $status] = self::exec([\PHP_BINARY, '-l', $this->directory . '/' . $path]);
            self::assertSame(0, $status, $path . ' does not parse: ' . $output);
        }

        $loader = $this->directory . '/load-everything.php';
        \file_put_contents($loader, <<<'PHP'
            <?php
            require $argv[1];
            require $argv[2] . '/autoload.php';
            foreach (glob($argv[2] . '/*.php') as $file) {
                if (!in_array(basename($file), ['autoload.php', 'load-everything.php'], true)) {
                    require_once $file;
                }
            }
            $markers = array_keys(array_filter(get_defined_constants(true)['user'] ?? [], static fn(string $name): bool => str_starts_with($name, 'MARKER_'), ARRAY_FILTER_USE_KEY));
            echo json_encode($markers);
            PHP);
        [$output, $status] = self::exec([\PHP_BINARY, $loader, \dirname(__DIR__, 2) . '/vendor/autoload.php', $this->directory]);

        self::assertSame(0, $status, 'loading the generated files failed: ' . $output);
        self::assertSame('[]', $output, 'loading the generated files ran code from the document');
        self::assertFileDoesNotExist($this->directory . '/marker-touched');

        $enums = $files['enums.php'];
        self::assertStringContainsString("    case Upcoming = 'upcoming';\n", $enums, 'an unusable varname falls back to the name derived from the value');
        self::assertStringContainsString("    case Released = 'released';\n", $enums, 'a reserved varname falls back too');
        self::assertStringContainsString('    case Marker = ' . Php::string(self::ENUM_VALUE) . ";\n", $enums, 'the value itself is kept exactly');
        self::assertNotEmpty(\array_filter($generator->warnings(), static fn(string $warning): bool => \str_contains($warning, 'x-enum-varnames')), 'the fallback is reported');

        self::assertStringContainsString(
            ' * @phpstan-type AlbumStatusInput AlbumStatus|"x*\\x2F define(\\"MARKER_ENUM_VALUE\\", 1); /*"|"y\\x0A*\\x2F define(\\"MARKER_NEWLINE_VALUE\\", 1); /*"|\'released\'|\'upcoming\'|\'withdrawn\'' . "\n",
            $files['resource-albums.php'],
            'a literal type that would close the docblock is escaped, and PHPStan reads back the exact value',
        );
    }

    public function testAnEnumComponentNameThatIsNotAClassNameIsRefused(): void
    {
        $document = self::fixture(static function (array $document): array {
            $document['components']['schemas']['Status: string {} define("MARKER", 1); enum Other'] = ['type' => 'string', 'enum' => ['a']];

            return $document;
        });

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('components.schemas.Status: string {}');

        DescriptorBuilder::build($document);
    }

    public function testAnErrorComponentNameThatIsNotAClassNameIsRefused(): void
    {
        $document = self::fixture(static function (array $document): array {
            $document['components']['schemas']['X {} define("MARKER", 1); class YError'] = [
                'title' => 'Teapot',
                'allOf' => [
                    ['$ref' => '#/components/schemas/Error'],
                    ['type' => 'object', 'properties' => ['code' => ['type' => 'string', 'const' => 'TEAPOT'], 'status' => ['type' => 'string', 'const' => '418']]],
                ],
            ];

            return $document;
        });

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('class YError');

        DescriptorBuilder::build($document);
    }

    public function testATypeThatCannotNameAClassIsRefused(): void
    {
        $document = self::fixture(static function (array $document): array {
            $document['components']['schemas']['ListsResource'] = [
                'type' => 'object',
                'properties' => ['type' => ['type' => 'string', 'const' => 'lists'], 'id' => ['type' => 'string']],
                'required' => ['type', 'id'],
            ];

            return $document;
        });

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('"lists"');

        Generator::default()->generate(
            DescriptorBuilder::build($document),
            new GeneratorConfig(self::NAMESPACE, 'Strings', PhpTarget::running()),
            'test',
        );
    }

    public function testATypeWithASemicolonCannotNameAFile(): void
    {
        $document = self::fixture(static function (array $document): array {
            $document['components']['schemas']['WidgetsResource'] = [
                'type' => 'object',
                'properties' => ['type' => ['type' => 'string', 'const' => 'widgets;define("MARKER", 1)'], 'id' => ['type' => 'string']],
                'required' => ['type', 'id'],
            ];
            $document['paths']['/widgets'] = ['get' => ['responses' => ['200' => ['content' => ['application/vnd.api+json' => ['schema' => [
                'type' => 'object',
                'properties' => ['data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WidgetsResource']]],
            ]]]]]]];

            return $document;
        });

        $this->expectException(\InvalidArgumentException::class);

        Generator::default()->generate(
            DescriptorBuilder::build($document),
            new GeneratorConfig(self::NAMESPACE, 'Strings', PhpTarget::running()),
            'test',
        );
    }

    private static function document(): SpecDocument
    {
        return self::fixture(static function (array $document): array {
            $document['components']['schemas']['AlbumStatus'] = [
                'description' => self::PROSE,
                'enum' => ['upcoming', 'released', 'withdrawn', self::ENUM_VALUE, self::NEWLINE_VALUE],
                'x-enum-varnames' => [self::VARNAME, 'class', 'Withdrawn', 'Marker', 'NewlineMarker'],
                'x-enum-descriptions' => ['Announced.', 'Released.', 'Withdrawn.', self::PROSE, 'A value with a newline in it.'],
            ];

            $document['components']['schemas']['PlaylistsAttributes']['properties']['slug']['pattern'] = "^a*/ define(\"MARKER_PATTERN\", 1); /*\n$";

            $document['components']['schemas']['ReleasesAttributes']['properties']['format']['oneOf'][0] = [
                'type' => 'object',
                'properties' => [
                    self::MEMBER => ['type' => 'integer', 'minimum' => 16, 'maximum' => 78],
                    'coloured' => ['type' => ['boolean', 'null']],
                    'medium' => ['type' => 'string', 'const' => self::DISCRIMINATOR],
                ],
                'required' => [self::MEMBER, 'coloured', 'medium'],
            ];

            $document['components']['schemas']['TeapotError'] = [
                'title' => 'Teapot*/ define("MARKER_TITLE", 1); /*',
                'description' => self::PROSE,
                'allOf' => [
                    ['$ref' => '#/components/schemas/Error'],
                    ['type' => 'object', 'properties' => [
                        'code' => ['type' => 'string', 'const' => "TEAPOT'*/ define(\"MARKER_CODE\", 1); /*"],
                        'status' => ['type' => 'string', 'const' => '418'],
                        'detail' => ['type' => 'string', 'example' => "Short\n*/ define(\"MARKER_EXAMPLE\", 1); /*"],
                    ]],
                ],
            ];

            return $document;
        });
    }

    /** The music-catalog document after `$edit`, which takes and returns it decoded. */
    private static function fixture(\Closure $edit): SpecDocument
    {
        $json = \file_get_contents(Fixtures::path('music-catalog.openapi.json'));
        self::assertIsString($json);
        $document = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        return SpecDocument::fromDecoded($edit($document));
    }

    /**
     * @param list<string> $command
     *
     * @return array{string, int}
     */
    private static function exec(array $command): array
    {
        $process = \proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        self::assertIsResource($process);
        $output = \stream_get_contents($pipes[1]) . \stream_get_contents($pipes[2]);
        \fclose($pipes[1]);
        \fclose($pipes[2]);

        return [\trim($output), \proc_close($process)];
    }
}
