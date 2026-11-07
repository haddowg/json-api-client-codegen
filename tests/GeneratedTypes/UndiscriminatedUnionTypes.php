<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiCodegen\Tests\Golden\Unions\Article;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\ArticleShapeCircle;
use haddowg\JsonApiCodegen\Tests\Golden\Unions\ArticleShapeRectangle;

use function PHPStan\Testing\assertType;

/**
 * An undiscriminated union of the `unions` golden output, checked at level 9: its read narrows
 * to a branch class, and its write takes a plain union of the branches' array shapes.
 *
 * Analysed by `composer phpstan-generated` and never executed. Each ignored line is an input
 * PHPStan must reject: an ignore that matches no error fails the analysis, so the rejection is
 * asserted as firmly as the accepted calls. A value mixing two branches is not among them,
 * since PHPStan's default array shapes admit extra keys; the server rejects it instead.
 */
final class UndiscriminatedUnionTypes
{
    public function theReadNarrowsToABranchClass(Article $article): int
    {
        $shape = $article->shape();
        assertType(ArticleShapeCircle::class . '|' . ArticleShapeRectangle::class . '|null', $shape);

        return match (true) {
            $shape instanceof ArticleShapeCircle => $shape->radius,
            $shape instanceof ArticleShapeRectangle => $shape->width * $shape->height,
            default => 0,
        };
    }

    public function theWriteTakesEitherBranchShape(): void
    {
        Article::update(['shape' => ['radius' => 2]]);
        Article::update(['shape' => ['width' => 3, 'height' => 4]]);
        Article::update(['shape' => null]);
    }

    public function theWriteRejectsWhatNoBranchAllows(): void
    {
        // @phpstan-ignore argument.type
        Article::update(['shape' => ['width' => 3]]);
        // @phpstan-ignore argument.type
        Article::update(['shape' => ['radius' => 'two']]);
    }
}
