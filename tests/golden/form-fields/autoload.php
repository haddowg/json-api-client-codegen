<?php

declare(strict_types=1);

/*
 * Loads the generated FormFields client. Classes share a file, one file per resource type,
 * so this map is how each one is found. Register it once through composer.json's
 * `autoload.files`; regenerating rewrites it, so a new type never needs a `dump-autoload`.
 *
 * @generated from an OpenAPI document with sha256 738b7dbae0e0beb66cae76a5ce2d7604c789d0e2bbb38f037a174d02235a6cd1
 */

\spl_autoload_register(static function (string $class): void {
    static $map = [
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Article' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleActions' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleAttachmentsRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleAuthorRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleBase' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleBlockHeading' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleBlockImage' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleCollectionActions' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleCoverRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleCreate' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleCreateBuilder' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleEditorRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleHandle' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleHasAuthor' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleHasTags' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleLockedRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleProjecting' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleProjection' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticlePublisherRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleQuery' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleSummary' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleTagsEdge' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleTagsPivot' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleTagsRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ArticleUpdate' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Articles' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\FormFieldsClient' => 'client.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\FormFieldsErrorCodes' => 'errors.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Healthcheck' => 'resource-healthcheck.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\HealthcheckBase' => 'resource-healthcheck.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Image' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ImageBase' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ImageClipsRelation' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ImageHandle' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ImageProjecting' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ImageProjection' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\ImageQuery' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Images' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\People' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleAccessor' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleBase' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleCompanyRelation' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleCreate' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleCreateBuilder' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleHandle' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleProjecting' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleProjection' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleQuery' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\PeopleUpdate' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Priority' => 'enums.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Status' => 'enums.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\SubscriptionExpiredError' => 'errors.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Tag' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagArticlesRelation' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagBase' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagCreate' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagCreateBuilder' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagHandle' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagProjecting' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagProjection' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagQuery' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\TagUpdate' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Tags' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Video' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoBase' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoCreate' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoCreateBuilder' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoHandle' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoProjecting' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoProjection' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoQuery' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\VideoUpdate' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\FormFields\\Videos' => 'resource-videos.php',
    ];

    if (isset($map[$class])) {
        require_once __DIR__ . '/' . $map[$class];
    }
});
