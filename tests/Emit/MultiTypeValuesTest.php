<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\ReadModel\Guard;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Emit\ReadModel\Shape;
use haddowg\JsonApiCodegen\Emit\Writes\WriteModel;
use haddowg\JsonApiCodegen\Tests\Golden\MultiTypes\Article;
use haddowg\JsonApiCodegen\Tests\Golden\MultiTypes\ArticleShapeCircle;
use haddowg\JsonApiCodegen\Tests\Golden\MultiTypes\ArticleTagsPivot;
use haddowg\JsonApiCodegen\Tests\Golden\MultiTypes\ArticleUpdate;
use haddowg\JsonApiCodegen\Tests\Support\GeneratedOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * A schema whose `type` is an array keeps every type it lists. Core projects a primitive `Union`
 * field that way, with each keyword holding only for values of its own JSON type, so
 * `["string", "number", "boolean"]` with a `minLength` reads as `non-empty-string|float|bool`
 * and a number comes back as a float, as a `number` attribute's does.
 *
 * The `multi-types` suite of `GeneratedOutput` is the witness with such values added to
 * `articles` (see {@see GeneratedOutput::multiTypesJson()}), generated on the 8.3 target so its
 * reads run on any supported PHP. `composer phpstan-generated` analyses it at level 9 beside
 * `tests/GeneratedTypes/MultiTypeTypes.php`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(Shape::class)]
#[CoversClass(Guard::class)]
#[CoversClass(ReadModelEmitter::class)]
#[CoversClass(WriteModel::class)]
final class MultiTypeValuesTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        GeneratedOutput::load('multi-types');
    }

    public function testEveryTypeReachesTheAccessorsSignature(): void
    {
        $articles = GeneratedOutput::files('multi-types')['resource-articles.php'];

        self::assertSame('string|float|bool|list<string|float|bool>|null', self::returned($articles, 'value'));
        self::assertStringContainsString('public function value(): string|float|bool|array|null', $articles);
        self::assertSame('non-empty-string|float|bool', self::returned($articles, 'defaultValue'));
        self::assertStringContainsString('public function defaultValue(): string|float|bool', $articles);
        self::assertStringContainsString('public function fallbackValue(): string|float|bool|null', $articles);
        self::assertSame('positive-int|non-empty-string|null', self::returned($articles, 'limit'));
        self::assertStringContainsString('public function limit(): int|string|null', $articles);
        self::assertSame('ArticleSourceShape', self::returned($articles, 'source'));
        self::assertStringContainsString('public function source(): string|array', $articles);
        self::assertSame('string|int<0, max>', self::returned($articles, 'mode'));
        self::assertStringContainsString('public function mode(): string|int', $articles);
        self::assertStringContainsString('As a string, at most 200 characters.', $articles);
        self::assertStringContainsString('@phpstan-type ArticleColumnsShape list<array{name: string, from: string|float|bool, to?: string|float|bool|null, values?: string|float|bool|list<string|float|bool>}>', $articles);
        self::assertStringContainsString('public function weight(): int|string', $articles);
    }

    public function testEveryTypeReachesTheWrites(): void
    {
        $articles = GeneratedOutput::files('multi-types')['resource-articles.php'];

        self::assertStringContainsString('public readonly string|float|bool|array|Missing|null $value = Missing::Value,', $articles);
        self::assertStringContainsString('public readonly string|float|bool|Missing $defaultValue = Missing::Value,', $articles);
        self::assertStringContainsString('public readonly int|string|Missing $weight = Missing::Value,', $articles);
    }

    public function testAScalarOfEachTypeIsReadAndANumberAsAFloat(): void
    {
        self::assertSame('on', self::article(['defaultValue' => 'on'])->defaultValue());
        self::assertSame(3.0, self::article(['defaultValue' => 3])->defaultValue());
        self::assertSame(1.5, self::article(['defaultValue' => 1.5])->defaultValue());
        self::assertTrue(self::article(['defaultValue' => true])->defaultValue());
        self::assertNull(self::article(['fallbackValue' => null])->fallbackValue());
        self::assertFalse(self::article(['fallbackValue' => false])->fallbackValue());
    }

    public function testAnIntegerStaysAnIntegerBesideAString(): void
    {
        self::assertSame(5, self::article(['limit' => 5])->limit());
        self::assertSame('all', self::article(['limit' => 'all'])->limit());
        self::assertSame(0, self::article(['mode' => 0])->mode());
        self::assertSame('auto', self::article(['mode' => 'auto'])->mode());
    }

    public function testAMultiTypeMixingScalarsWithAListReadsEither(): void
    {
        self::assertSame('x', self::article(['value' => 'x'])->value());
        self::assertSame(2.0, self::article(['value' => 2])->value());
        self::assertSame([1.0, 'a', false], self::article(['value' => [1, 'a', false]])->value());
        self::assertNull(self::article(['value' => null])->value());
    }

    public function testAnItemOfNoListedTypeIsDroppedFromANestedList(): void
    {
        self::assertSame(['a', 3.0], self::article(['value' => [['nested'], 'a', 3]])->value());
    }

    public function testAStringOrAnObjectReadsEither(): void
    {
        self::assertSame('https://example.com', self::article(['source' => 'https://example.com'])->source());
        self::assertSame(['url' => 'https://example.com'], self::article(['source' => ['url' => 'https://example.com']])->source());
    }

    public function testAValueOfNoListedTypeIsMalformed(): void
    {
        foreach ([['defaultValue', ['x']], ['value', ['a' => 1]], ['limit', 1.5], ['mode', true]] as [$attribute, $value]) {
            try {
                $article = self::article([$attribute => $value]);
                match ($attribute) {
                    'defaultValue' => $article->defaultValue(),
                    'value' => $article->value(),
                    'limit' => $article->limit(),
                    default => $article->mode(),
                };
                self::fail('Expected MalformedDocument for ' . $attribute . ' ' . \json_encode($value, \JSON_THROW_ON_ERROR));
            } catch (MalformedDocument) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testAMultiTypeMemberOfAListedObjectKeepsEveryType(): void
    {
        $columns = self::article(['columns' => [
            ['name' => 'a', 'from' => 1, 'to' => false],
            ['name' => 'b', 'from' => 'x', 'to' => ['dropped']],
            ['name' => 'c', 'from' => true, 'values' => [1, 'y']],
            ['name' => 'd', 'from' => 0, 'values' => 'z'],
            ['name' => 'e', 'from' => 0, 'values' => ['k' => 'v']],
        ]])->columns();

        self::assertSame([
            ['name' => 'a', 'from' => 1.0, 'to' => false],
            ['name' => 'b', 'from' => 'x'],
            ['name' => 'c', 'from' => true, 'values' => [1.0, 'y']],
            ['name' => 'd', 'from' => 0.0, 'values' => 'z'],
            ['name' => 'e', 'from' => 0.0],
        ], $columns);
    }

    public function testARequiredMultiTypeMemberOfAnotherTypeIsMalformed(): void
    {
        $this->expectException(MalformedDocument::class);
        $this->expectExceptionMessage('has a "from" member that is not a string, a number or a boolean.');

        self::article(['columns' => [['name' => 'a', 'from' => ['x']]]])->columns();
    }

    public function testAMultiTypeMemberOfAUnionBranchKeepsEveryType(): void
    {
        $articles = GeneratedOutput::files('multi-types')['resource-articles.php'];
        self::assertStringContainsString('@param string|list<string>|null $label', $articles);

        $listed = self::article(['shape' => ['radius' => 1, 'label' => ['a', 'b']]])->shape();
        $named = self::article(['shape' => ['radius' => 1, 'label' => 'a']])->shape();

        self::assertInstanceOf(ArticleShapeCircle::class, $listed);
        self::assertInstanceOf(ArticleShapeCircle::class, $named);
        self::assertSame(['a', 'b'], $listed->label);
        self::assertSame('a', $named->label);
    }

    public function testAMultiTypePivotFieldReadsEitherType(): void
    {
        $raw = ['type' => 'articles', 'id' => '1', 'attributes' => [], 'relationships' => ['tags' => ['data' => [
            ['type' => 'tags', 'id' => '1', 'meta' => ['pivot' => ['position' => 1, 'primary' => null, 'taggedAt' => '2026-01-02T03:04:05+00:00', 'taggedBy' => null, 'weight' => 2]]],
            ['type' => 'tags', 'id' => '2', 'meta' => ['pivot' => ['position' => 2, 'primary' => null, 'taggedAt' => '2026-01-02T03:04:05+00:00', 'taggedBy' => null, 'weight' => 'heavy']]],
        ]]]];
        $pivots = Article::_hydrate($raw, ResourceContext::of(['data' => $raw]))->tagsPivots();

        self::assertContainsOnlyInstancesOf(ArticleTagsPivot::class, $pivots);
        self::assertSame([2, 'heavy'], \array_map(static fn(ArticleTagsPivot $pivot): int|string => $pivot->weight(), $pivots));
    }

    public function testAWriteSendsAValueOfAnyListedType(): void
    {
        $document = ArticleUpdate::from(['value' => [1, 'a'], 'defaultValue' => true, 'limit' => 'all'])->toDocument('1');

        self::assertSame(['data' => ['type' => 'articles', 'id' => '1', 'attributes' => ['value' => [1, 'a'], 'defaultValue' => true, 'limit' => 'all']]], $document);
    }

    /** The `@return` type of the accessor `$method` in the generated `$source`. */
    private static function returned(string $source, string $method): ?string
    {
        $found = \preg_match('/@return (.+)\n(?:     \*.*\n)*?     \*\/\n    public function ' . $method . '\(\)/', $source, $match);

        return $found === 1 ? $match[1] : null;
    }

    /**
     * An article whose response carries `$attributes`.
     *
     * @param array<string, mixed> $attributes
     */
    private static function article(array $attributes): Article
    {
        $raw = ['type' => 'articles', 'id' => '1', 'attributes' => $attributes];

        return Article::_hydrate($raw, ResourceContext::of(['data' => $raw]));
    }
}
