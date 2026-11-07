<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\Article;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\ArticleStepsFindReplace;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\ArticleStepsTrim;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * A list attribute whose items are objects reads as a list of array shapes narrowed member by
 * member, and one whose items are a union as a list of the union's branch classes. The
 * witness's `articles` has both, `sources` and `steps`; the reads run from
 * `tests/golden/unions`, the witness generated for PHP 8.3.
 */
#[CoversClass(ReadModelEmitter::class)]
final class ContractTwentyOneTest extends TestCase
{
    public function testAListOfObjectsReadsEachElementAsItsShape(): void
    {
        $sources = self::article(['sources' => [
            ['href' => 'https://example.test/a', 'retrievedAt' => null],
            ['href' => 'https://example.test/b', 'retrievedAt' => '2026-01-02T03:04:05+00:00'],
        ]])->sources();

        self::assertSame([
            ['href' => 'https://example.test/a', 'retrievedAt' => null],
            ['href' => 'https://example.test/b', 'retrievedAt' => '2026-01-02T03:04:05+00:00'],
        ], $sources);
        self::assertNull(self::article(['sources' => null])->sources());
    }

    public function testAnElementMissingARequiredMemberIsMalformed(): void
    {
        $this->expectException(MalformedDocument::class);

        self::article(['sources' => [['retrievedAt' => null]]])->sources();
    }

    public function testAListOfAUnionReadsEachElementIntoItsBranchClass(): void
    {
        $steps = self::article(['steps' => [
            ['operation' => 'trim'],
            ['operation' => 'find_replace', 'params' => ['find' => 'colour', 'replace' => 'color']],
        ]])->steps();

        self::assertNotNull($steps);
        self::assertCount(2, $steps);
        self::assertInstanceOf(ArticleStepsTrim::class, $steps[0]);
        self::assertInstanceOf(ArticleStepsFindReplace::class, $steps[1]);
    }

    public function testAnElementNamingNoBranchIsMalformed(): void
    {
        $this->expectException(MalformedDocument::class);
        $this->expectExceptionMessage('The "steps" attribute of an "articles" resource has a "operation" member that is not one of "trim", "find_replace".');

        self::article(['steps' => [['operation' => 'uppercase']]])->steps();
    }

    public function testAnElementThatIsNotAnObjectIsMalformed(): void
    {
        $this->expectException(MalformedDocument::class);

        self::article(['steps' => ['trim']])->steps();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private static function article(array $attributes): Article
    {
        require_once \dirname(__DIR__) . '/golden/unions/autoload.php';
        $raw = ['type' => 'articles', 'id' => '1', 'attributes' => $attributes];

        return Article::_hydrate($raw, ResourceContext::of(['data' => $raw]));
    }
}
