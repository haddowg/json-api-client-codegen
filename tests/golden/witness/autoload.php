<?php

declare(strict_types=1);

/*
 * Loads the generated Witness client. Classes share a file, one file per resource type,
 * so this map is how each one is found. Register it once through composer.json's
 * `autoload.files`; regenerating rewrites it, so a new type never needs a `dump-autoload`.
 *
 * @generated from an OpenAPI document with sha256 511bb2dd87505e8dedd0cad0b4382d56693be9dd197f1e939a5ea20a31363db4
 */

\spl_autoload_register(static function (string $class): void {
    static $map = [
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Article' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleActions' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleAttachmentsRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleAuthorRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleBase' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleBlockHeading' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleBlockImage' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleCollectionActions' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleCoverRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleCreate' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleCreateBuilder' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleEditorRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleGalleryRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleHandle' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleHasAuthor' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleHasTags' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleLockedRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleProjecting' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleProjection' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticlePublisherRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleQuery' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleSubjectRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleSummary' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleTagsEdge' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleTagsPivot' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleTagsRelation' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ArticleUpdate' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Articles' => 'resource-articles.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Healthcheck' => 'resource-healthcheck.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\HealthcheckBase' => 'resource-healthcheck.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Image' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageBase' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageClipsRelation' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageDownloadLink' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageDownloadLinkMeta' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageHandle' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageLinks' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageProjecting' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageProjection' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\ImageQuery' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Images' => 'resource-images.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\People' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleAccessor' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleBase' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleCompanyRelation' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleCreate' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleCreateBuilder' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleHandle' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleProjecting' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleProjection' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleQuery' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\PeopleUpdate' => 'resource-people.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Priority' => 'enums.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Status' => 'enums.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\SubscriptionExpiredError' => 'errors.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Tag' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagArticlesRelation' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagBase' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagCreate' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagCreateBuilder' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagHandle' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagProjecting' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagProjection' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagQuery' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\TagUpdate' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Tags' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Video' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoBase' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoCreate' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoCreateBuilder' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoHandle' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoProjecting' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoProjection' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoQuery' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\VideoUpdate' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\Videos' => 'resource-videos.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\WitnessClient' => 'client.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Witness\\WitnessErrorCodes' => 'errors.php',
    ];

    if (isset($map[$class])) {
        require_once __DIR__ . '/' . $map[$class];
    }
});
