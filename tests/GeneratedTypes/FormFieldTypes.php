<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\GeneratedTypes;

use haddowg\JsonApiClient\Http\FilePart;
use haddowg\JsonApiCodegen\Tests\Golden\FormFields\FormFieldsClient;

/**
 * The fields a form body of the `form-fields` golden output takes once its schema lists them,
 * checked at level 9.
 *
 * Analysed by `composer phpstan-generated` and never executed. Each ignored line is a body
 * PHPStan must reject: an ignore that matches no error fails the analysis, so the rejection is
 * asserted as firmly as the accepted calls.
 */
final class FormFieldTypes
{
    public function aMultipartBodyTakesItsDeclaredFields(FormFieldsClient $client): void
    {
        $actions = $client->articles->actions();

        $actions->bulkImport('multipart/form-data', ['title' => 'x']);
        $actions->bulkImport('multipart/form-data', [
            'title' => 'x',
            'cover' => new FilePart('bytes', 'cover.png', 'image/png'),
            'tags' => ['a', 'b'],
            'rows' => 3,
            'status' => 'draft',
        ]);
    }

    public function aMultipartBodyRejectsWhatItsSchemaRulesOut(FormFieldsClient $client): void
    {
        $actions = $client->articles->actions();

        // @phpstan-ignore argument.type
        $actions->bulkImport('multipart/form-data', ['cover' => new FilePart('bytes', 'cover.png')]);
        // @phpstan-ignore argument.type
        $actions->bulkImport('multipart/form-data', ['title' => 'x', 'rows' => 'three']);
        // @phpstan-ignore argument.type
        $actions->bulkImport('multipart/form-data', ['title' => 'x', 'tags' => 'a']);
        // @phpstan-ignore argument.type
        $actions->bulkImport('multipart/form-data', ['title' => 'x', 'status' => 'archived']);
    }

    public function aUrlencodedBodyTakesTheExtraFieldsItsSchemaAllows(FormFieldsClient $client): void
    {
        $actions = $client->articles->actions();

        $actions->bulkImport('application/x-www-form-urlencoded', ['title' => 'x', 'rows' => 3]);
        // @phpstan-ignore argument.type
        $actions->bulkImport('application/x-www-form-urlencoded', ['title' => 'x', 'rows' => 'three']);
    }
}
