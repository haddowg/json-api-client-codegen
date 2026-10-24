<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Emit\Identifier;
use haddowg\JsonApiCodegen\Emit\Php;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Php::class)]
#[CoversClass(Identifier::class)]
final class PhpTest extends TestCase
{
    public function testADocblockLineCannotCloseTheCommentOrStartANewLine(): void
    {
        self::assertSame(
            "/**\n * a*\\/ b c\n *\n * @var x*\\/ y\n */\n",
            Php::docblock(["a*/ b\nc", '', "@var x*/\ry"], 0, false),
        );
        self::assertSame("    /** a*\\/ b */\n", Php::docblock(['a*/ b'], 4));
        self::assertSame('/** @var T a b */', Php::comment("@var T a\nb"));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function literals(): iterable
    {
        yield 'plain' => ['released', "'released'"];
        yield 'quote and backslash' => ["it's a\\b", "'it\\'s a\\\\b'"];
        yield 'comment close' => ['x*/y', '"x*\\x2Fy"'];
        yield 'line break' => ["a\nb", '"a\\x0Ab"'];
        yield 'double quote and dollar' => ["\"\$*/", '"\\"\\$*\\x2F"'];
    }

    #[DataProvider('literals')]
    public function testADocStringIsAPhpDocLiteralOfTheExactValue(string $value, string $literal): void
    {
        self::assertSame($literal, Php::docString($value));
        self::assertStringNotContainsString('*/', Php::docString($value));
    }

    public function testADocKeyIsBareOnlyWhenItIsAnIdentifier(): void
    {
        self::assertSame('title', Php::docKey('title'));
        self::assertSame("'release-info'", Php::docKey('release-info'));
        self::assertSame('"a*\\x2F"', Php::docKey('a*/'));
    }

    public function testIdentifiersAreCheckedForTheirPosition(): void
    {
        self::assertFalse(Identifier::isValid('a b'));
        self::assertFalse(Identifier::isValid('1st'));
        self::assertFalse(Identifier::isValid("name\n"));
        self::assertFalse(Identifier::isClassName('List'));
        self::assertTrue(Identifier::isClassName('Enum'));
        self::assertFalse(Identifier::isConstantName('class'));
        self::assertTrue(Identifier::isConstantName('List'));
        self::assertFalse(Identifier::isMemberName('this'));
        self::assertTrue(Identifier::isMemberName('class'));
    }
}
