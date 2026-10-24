<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\EnumCase;
use haddowg\JsonApiCodegen\Descriptor\EnumDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Enums\EnumEmitter;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Tests\Support\ArtefactParity;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumEmitter::class)]
#[CoversClass(Php::class)]
final class EnumEmitterTest extends TestCase
{
    public function testAlbumStatusMatchesTheArtefact(): void
    {
        ArtefactParity::assertMatches('AlbumStatus', 'enums.php', Generated::file('enums.php'));
    }

    public function testACaseWithoutAVarnameIsNamedFromItsValue(): void
    {
        $api = new ApiDescriptor([], ['Visibility' => new EnumDescriptor('Visibility', [
            new EnumCase('public-read', null, null),
            new EnumCase('2fa', null, null),
        ], 'Who can see it.')], null, 2, []);

        $unit = [...(new EnumEmitter())->emit($api, Generated::config())][0];
        self::assertInstanceOf(ClassUnit::class, $unit);
        $source = $unit->source;

        self::assertStringContainsString("case PublicRead = 'public-read';", $source);
        self::assertStringContainsString("case Case2fa = '2fa';", $source);
        self::assertStringContainsString(' * Who can see it.', $source, 'a description that is not the case table is the summary');
    }

    public function testADerivedCaseNameAvoidsReservedAndTakenNames(): void
    {
        $api = new ApiDescriptor([], ['Keyword' => new EnumDescriptor('Keyword', [
            new EnumCase('class', null, null),
            new EnumCase('a-b', null, null),
            new EnumCase('a_b', null, null),
        ], null)], null, 2, []);

        $unit = [...(new EnumEmitter())->emit($api, Generated::config())][0];
        self::assertInstanceOf(ClassUnit::class, $unit);

        self::assertStringContainsString("case CaseClass = 'class';", $unit->source);
        self::assertStringContainsString("case AB = 'a-b';", $unit->source);
        self::assertStringContainsString("case AB2 = 'a_b';", $unit->source);
    }
}
