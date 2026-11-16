<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiCodegen\Tests\Golden\MultiTypes\Article;
use haddowg\JsonApiCodegen\Tests\Golden\MultiTypes\ArticleTagsPivot;

use function PHPStan\Testing\assertType;

/**
 * The multi-type values of the `multi-types` generated output, checked at level 9: a read keeps
 * every type its schema lists, and a write takes a value of any of them and nothing else.
 *
 * Analysed by `composer phpstan-generated` and never executed. Each ignored line is an input
 * PHPStan must reject: an ignore that matches no error fails the analysis.
 */
final class MultiTypeTypes
{
    public function eachReadKeepsEveryType(Article $article, ArticleTagsPivot $pivot): void
    {
        assertType('bool|float|list<bool|float|string>|string|null', $article->value());
        assertType('bool|float|non-empty-string', $article->defaultValue());
        assertType('bool|float|string|null', $article->fallbackValue());
        assertType('int<1, max>|non-empty-string|null', $article->limit());
        assertType('array{url: string}|string', $article->source());
        assertType('int<0, max>|string', $article->mode());
        assertType('list<array{name: string, from: bool|float|string, to?: bool|float|string|null, values?: bool|float|list<bool|float|string>|string}>', $article->columns());
        assertType('int|string', $pivot->weight());
    }

    public function theWriteTakesAnyListedType(): void
    {
        Article::update(['value' => 'x', 'defaultValue' => 1.5, 'fallbackValue' => null, 'limit' => 'all', 'source' => ['url' => 'https://example.com'], 'mode' => 2]);
        Article::update(['value' => [1, 'a', false], 'defaultValue' => true, 'limit' => 5, 'source' => 'https://example.com', 'mode' => 'auto']);
        Article::update(['columns' => [['name' => 'a', 'from' => 1, 'to' => false]]]);
    }

    public function theWriteRejectsAnUnlistedType(): void
    {
        // @phpstan-ignore argument.type
        Article::update(['defaultValue' => ['x']]);
        // @phpstan-ignore argument.type
        Article::update(['limit' => 1.5]);
        // @phpstan-ignore argument.type
        Article::update(['columns' => [['name' => 'a', 'from' => ['x']]]]);
    }
}
