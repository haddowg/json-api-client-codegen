<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Tests\Support\GeneratedOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The generated output for `paginators.openapi.json`, a hand-built contract-3 document with one
 * collection per pagination strategy: renamed keys, a strategy menu, an unstamped fallback and a
 * strategy this codegen does not know.
 *
 * Its output, the `paginators` suite of `GeneratedOutput`, is analysed at level 9 by `composer phpstan-generated`, beside
 * `tests/GeneratedTypes`, which asserts the page type each picker leaves a menu query reading.
 */
#[CoversClass(Generator::class)]
final class PaginatorOutputTest extends TestCase
{
    public const string NAMESPACE = 'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators';

    public function testOnlyTheUnknownStrategyIsWorthAWarning(): void
    {
        // Renamed keys and a menu are what the stamp describes, so they generate silently.
        self::assertSame(
            ['`GET /logs` paginates by the `keyset` strategy, which this codegen does not know, so its reads are generated unpaginated.'],
            self::generate()[1],
        );
    }

    public function testTheMenuQueryStartsOnTheDefaultArmsPage(): void
    {
        $posts = self::generate()[0]['resource-posts.php'];

        self::assertStringContainsString("/** @var PostQuery<PostBase, Cursor> */\n        return new PostQuery(\$this->fetcher, Cursor::read(...));", $posts);
        self::assertStringContainsString("return \$this->choose(['kind' => 'numbered', 'number' => \$number, 'size' => \$size], PageNumber::read(...));", $posts);
        self::assertStringNotContainsString('use PostProjecting;' . "\n    use QueryConditionable;", $posts, 'the menu query writes its withX() inline');
    }

    public function testTheMenuOnARelatedReadIsTypedAsItsDefault(): void
    {
        // A relation read picks no strategy, so it gets the server's default: the cursor arm.
        self::assertStringContainsString('@return PaginatedCollection<Comment, Cursor>', self::generate()[0]['resource-posts.php']);
    }

    /**
     * @return array{array<string, string>, list<string>}
     */
    private static function generate(): array
    {
        return [GeneratedOutput::files('paginators'), GeneratedOutput::warnings('paginators')];
    }
}
