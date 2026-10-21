<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Emit\Assembler;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\UnitKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Assembler::class)]
#[CoversClass(ClassUnit::class)]
#[CoversClass(FileKey::class)]
#[CoversClass(Contribution::class)]
final class AssemblerTest extends TestCase
{
    public function testDeclarationsAreOrderedSoEveryParentIsDeclaredFirst(): void
    {
        $file = FileKey::resource('albums');
        $files = Assembler::assemble([
            new ClassUnit('Albums', UnitKind::FinalClass, $file, "final class Albums {}\n"),
            new ClassUnit('Album', UnitKind::FinalClass, $file, "final class Album extends AlbumBase {}\n", primary: true),
            new ClassUnit('AlbumBase', UnitKind::AbstractClass, $file, "abstract class AlbumBase {}\n"),
            new ClassUnit('AlbumProjecting', UnitKind::Trait, $file, "trait AlbumProjecting {}\n"),
            new ClassUnit('AlbumHasArtist', UnitKind::Interface, $file, "interface AlbumHasArtist {}\n"),
        ], self::config(), 'h');

        $source = $files['resource-albums.php'];
        $order = [];
        foreach (['interface AlbumHasArtist', 'trait AlbumProjecting', 'abstract class AlbumBase', 'final class Album extends', 'final class Albums'] as $declaration) {
            $position = \strpos($source, $declaration);
            self::assertIsInt($position, $declaration);
            $order[] = $position;
        }
        $sorted = $order;
        \sort($sorted);

        self::assertSame($sorted, $order);
    }

    public function testImportsAreMergedSortedAndExcludeTheGeneratedNamespace(): void
    {
        $files = Assembler::assemble([
            new ClassUnit('Album', UnitKind::FinalClass, FileKey::resource('albums'), "final class Album {}\n", ['Psr\\Http\\Message\\StreamInterface', 'haddowg\\JsonApiClient\\Support\\Missing', 'App\\Api\\Artist']),
            new ClassUnit('AlbumBase', UnitKind::AbstractClass, FileKey::resource('albums'), "abstract class AlbumBase {}\n", ['\\haddowg\\JsonApiClient\\Support\\Missing']),
        ], self::config(), 'h');

        self::assertStringContainsString(
            "namespace App\\Api;\n\nuse haddowg\\JsonApiClient\\Support\\Missing;\nuse Psr\\Http\\Message\\StreamInterface;\n\n",
            $files['resource-albums.php'],
        );
    }

    public function testTheClassMapPointsEveryDeclarationAtItsFileAndCarriesTheSpecHash(): void
    {
        $files = Assembler::assemble([
            new ClassUnit('Album', UnitKind::FinalClass, FileKey::resource('albums'), "final class Album {}\n"),
            new ClassUnit('AlbumStatus', UnitKind::Enum, FileKey::ENUMS, "enum AlbumStatus: string {}\n"),
        ], self::config(), 'abc123');

        $map = $files[FileKey::AUTOLOAD];
        self::assertStringContainsString("'App\\\\Api\\\\Album' => 'resource-albums.php',", $map);
        self::assertStringContainsString("'App\\\\Api\\\\AlbumStatus' => 'enums.php',", $map);
        self::assertStringContainsString('sha256 abc123', $map);
        self::assertSame(['autoload.php', 'enums.php', 'resource-albums.php'], (static function (array $keys): array {
            \sort($keys);

            return $keys;
        })(\array_keys($files)));
    }

    public function testTwoDeclarationsWithOneNameAreRejected(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Two generated declarations are named "Album" and "album"');

        Assembler::assemble([
            new ClassUnit('Album', UnitKind::FinalClass, FileKey::resource('albums'), "final class Album {}\n"),
            new ClassUnit('album', UnitKind::FinalClass, FileKey::ENUMS, "final class album {}\n"),
        ], self::config(), 'h');
    }

    public function testAFileNamedLikeAClassIsRejected(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('would collide with class "Enums"');

        Assembler::assemble([
            new ClassUnit('Enums', UnitKind::FinalClass, FileKey::ENUMS, "final class Enums {}\n"),
        ], self::config(), 'h');
    }

    public function testATypeThatCannotNameAFileIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FileKey::resource('../albums');
    }

    public function testContributionsMergeIntoTheirTargetInOrder(): void
    {
        $owner = "/**\n * An album.\n *\n * @generated from `x`\n */\nfinal class Album\n{\n    public function title(): string\n    {\n        return '';\n    }\n}\n";

        $files = Assembler::assemble([
            new Contribution('Album', 20, ['@phpstan-type AlbumSortToken \'title\''], "    public const array SORT_TOKENS = ['title'];\n"),
            new ClassUnit('Album', UnitKind::FinalClass, FileKey::resource('albums'), $owner, primary: true),
            new Contribution('Album', 10, ['@phpstan-type AlbumCreateShape array{title: string}'], "    public const array WRITABLE = ['title'];\n", ['haddowg\\JsonApiClient\\Support\\Identifier']),
        ], self::config(), 'h');

        self::assertStringContainsString(
            " * An album.\n *\n * @phpstan-type AlbumCreateShape array{title: string}\n * @phpstan-type AlbumSortToken 'title'\n *\n * @generated from `x`\n */\nfinal class Album\n{\n    public const array WRITABLE = ['title'];\n\n    public const array SORT_TOKENS = ['title'];\n\n    public function title(): string",
            $files['resource-albums.php'],
        );
        self::assertStringContainsString("use haddowg\\JsonApiClient\\Support\\Identifier;\n", $files['resource-albums.php']);
    }

    public function testAContributionToAnUngeneratedDeclarationIsRejected(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('targets "Ghost"');

        Assembler::assemble([new Contribution('Ghost', 1, ['@phpstan-type X int'])], self::config(), 'h');
    }

    private static function config(): GeneratorConfig
    {
        return new GeneratorConfig('App\\Api', 'Api', PhpTarget::Php83);
    }
}
