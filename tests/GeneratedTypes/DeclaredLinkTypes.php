<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiCodegen\Tests\Golden\Witness\Article;
use haddowg\JsonApiCodegen\Tests\Golden\Witness\Image;

use function PHPStan\Testing\assertType;

/**
 * The types the declared links of the `witness` golden output read as, asserted at level 9.
 *
 * Analysed by `composer phpstan-generated` and never executed. Each ignored line is a read
 * PHPStan must reject: an ignore that matches no error fails the analysis.
 */
final class DeclaredLinkTypes
{
    public function aLinkObjectIsTypedThroughItsMeta(Image $image): void
    {
        $links = $image->_declaredLinks();

        assertType('haddowg\JsonApiCodegen\Tests\Golden\Witness\ImageDownloadLink|null', $links->download());
        assertType('string|null', $links->download()?->href());
        assertType('string|null', $links->download()?->title());
        assertType('string|null', $links->download()?->meta()->method());
        assertType('DateTimeImmutable|null', $links->download()?->meta()->expiresAt());
        assertType('array<string, mixed>|null', $links->download()?->meta()->headers());
    }

    public function anOptionalLinkMustBeCheckedBeforeItIsRead(Image $image): void
    {
        // @phpstan-ignore method.nonObject
        $image->_declaredLinks()->download()->href();

        $download = $image->_declaredLinks()->download();
        if ($download !== null) {
            assertType('string', $download->href());
            assertType('DateTimeImmutable', $download->meta()->expiresAt());
        }
    }

    public function theHooksReadTheSameTypes(Image $image): void
    {
        $links = $image->_declaredLinks();

        assertType('haddowg\JsonApiCodegen\Tests\Golden\Witness\ImageDownloadLink|null', $links->download);
        assertType('DateTimeImmutable|null', $links->download?->meta->expiresAt);
        assertType('string|null', $links->download?->href);
        assertType('string|null', $links->thumbnail);
    }

    public function aBareLinkIsItsUrl(Image $image): void
    {
        assertType('string|null', $image->_declaredLinks()->thumbnail());
    }

    public function anUndeclaredLinkStaysLoose(Image $image, Article $article): void
    {
        assertType('array<string, mixed>', $image->_links());
        assertType('string|null', $image->_link('elsewhere'));

        // @phpstan-ignore method.notFound
        $article->_declaredLinks();
    }
}
