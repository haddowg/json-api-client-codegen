<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\Constraints;
use haddowg\JsonApiCodegen\Emit\Narrowing;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Narrowing::class)]
final class NarrowingTest extends TestCase
{
    /**
     * @return iterable<string, array{Constraints, string}>
     */
    public static function integers(): iterable
    {
        yield 'unbounded' => [new Constraints(), 'int'];
        yield 'a floor of one' => [new Constraints(minimum: 1), 'positive-int'];
        yield 'a floor of zero keeps the open side' => [new Constraints(minimum: 0), 'int<0, max>'];
        yield 'a cap alone' => [new Constraints(maximum: 10), 'int<min, 10>'];
        yield 'both bounds' => [new Constraints(minimum: 16, maximum: 78), 'int<16, 78>'];
        yield 'exclusive bounds step inside' => [new Constraints(exclusiveMinimum: 0, exclusiveMaximum: 10), 'int<1, 9>'];
        yield 'the tighter bound wins' => [new Constraints(minimum: 1, exclusiveMinimum: 4), 'int<5, max>'];
        yield 'a fractional bound rounds inward' => [new Constraints(minimum: 1.5, maximum: 9.5), 'int<2, 9>'];
    }

    #[DataProvider('integers')]
    public function testAnIntegerIsNarrowedToItsBounds(Constraints $constraints, string $expected): void
    {
        self::assertSame($expected, Narrowing::scalar('integer', $constraints));
    }

    public function testAStringIsNonEmptyFromAMinimumLengthOfOne(): void
    {
        self::assertSame('string', Narrowing::scalar('string', new Constraints(maxLength: 10)));
        self::assertSame('string', Narrowing::scalar('string', new Constraints(minLength: 0)));
        self::assertSame('non-empty-string', Narrowing::scalar('string', new Constraints(minLength: 1)));
        self::assertSame('non-empty-string', Narrowing::scalar('string', new Constraints(minLength: 3)));
    }

    public function testAListIsNonEmptyFromAMinimumOfOneItem(): void
    {
        self::assertSame('list<int>', Narrowing::list('int', new Constraints(maxItems: 3)));
        self::assertSame('non-empty-list<int>', Narrowing::list('int', new Constraints(minItems: 1)));
    }

    public function testANumberIsNeverNarrowed(): void
    {
        self::assertSame('float', Narrowing::scalar('number', new Constraints(minimum: 0, maximum: 1)));
    }

    public function testWhatNoTypeStatesBecomesOneSentence(): void
    {
        self::assertNull(Narrowing::prose('string', new Constraints(minLength: 1)), 'non-empty-string says it all');
        self::assertNull(Narrowing::prose('integer', new Constraints(minimum: 1, maximum: 5)), 'int<1, 5> says it all');
        self::assertNull(Narrowing::prose('string', new Constraints(), 'date-time'), 'a date is coerced, not described');
        self::assertNull(Narrowing::prose('string', new Constraints(), 'string'), 'a format hint that is only the JSON type says nothing');

        self::assertSame('Between 3 and 120 characters; matches `^[a-z]+$`; in `email` format.', Narrowing::prose('string', new Constraints(minLength: 3, maxLength: 120, pattern: '^[a-z]+$'), 'email'));
        self::assertSame('Greater than 0 and at most 10.', Narrowing::prose('number', new Constraints(maximum: 10, exclusiveMinimum: 0)));
        self::assertSame('A multiple of 10.', Narrowing::prose('integer', new Constraints(minimum: 0, multipleOf: 10)));
        self::assertSame('Between 2 and 10 items; no duplicate items.', Narrowing::prose('array', new Constraints(minItems: 2, maxItems: 10, uniqueItems: true)));
        self::assertSame('At most 20 members.', Narrowing::prose('object', new Constraints(maxProperties: 20)));
    }

    public function testADefaultIsStatedOnlyWhereAskedFor(): void
    {
        $size = new Constraints(minimum: 1, maximum: 100, default: 15);

        self::assertNull(Narrowing::prose('integer', $size));
        self::assertSame('Defaults to 15.', Narrowing::prose('integer', $size, null, true));
    }
}
