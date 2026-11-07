<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiCodegen\Tests\Golden\Witness\ArticleLockedError;
use haddowg\JsonApiCodegen\Tests\Golden\Witness\InsufficientScopeError;
use haddowg\JsonApiCodegen\Tests\Golden\Witness\MediaTypeUnsupportedError;
use haddowg\JsonApiCodegen\Tests\Golden\Witness\RequestBodyInvalidJsonError;

use function PHPStan\Testing\assertType;

/**
 * The types the error code classes of the `witness` generated output read their `meta` as, asserted
 * at level 9.
 *
 * Analysed by `composer phpstan-generated` and never executed. Each ignored line is a read
 * PHPStan must reject: an ignore that matches no error fails the analysis.
 */
final class ErrorMetaTypes
{
    public function requiredMetaIsAlwaysThere(InsufficientScopeError $error): void
    {
        assertType('haddowg\JsonApiCodegen\Tests\Golden\Witness\InsufficientScopeMeta', $error->meta());
        assertType('non-empty-list<string>', $error->meta()->requiredScopes);
        assertType('non-empty-list<string>', $error->meta()->missingScopes);
        assertType('string|null', $error->meta()->include);
    }

    public function optionalMetaMustBeCheckedBeforeItIsRead(RequestBodyInvalidJsonError $error): void
    {
        assertType('haddowg\JsonApiCodegen\Tests\Golden\Witness\RequestBodyInvalidJsonMeta|null', $error->meta());
        assertType('string|null', $error->meta()?->original);

        // @phpstan-ignore property.nonObject
        $error->meta()->original;
    }

    public function aCodeDeclaringNoMetaHasNoMetaAccessor(ArticleLockedError $locked, MediaTypeUnsupportedError $media): void
    {
        assertType('array<string, mixed>', $locked->withCode(ArticleLockedError::CODE)->meta ?? []);

        // @phpstan-ignore method.notFound
        $locked->meta();

        // @phpstan-ignore method.notFound
        $media->parameters();
    }
}
