<?php

declare(strict_types=1);

/*
 * Loads the generated Paginators client. Classes share a file, one file per resource type,
 * so this map is how each one is found. Register it once through composer.json's
 * `autoload.files`; regenerating rewrites it, so a new type never needs a `dump-autoload`.
 *
 * @generated from an OpenAPI document with sha256 72a5ba22b9149f2544fdd34b85af3b2c29f3e3b2665f69c6836ee8f85957fbbb
 */

\spl_autoload_register(static function (string $class): void {
    static $map = [
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Chapter' => 'resource-chapters.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\ChapterBase' => 'resource-chapters.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\ChapterQuery' => 'resource-chapters.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Chapters' => 'resource-chapters.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Comment' => 'resource-comments.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\CommentBase' => 'resource-comments.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\CommentQuery' => 'resource-comments.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Comments' => 'resource-comments.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Event' => 'resource-events.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\EventBase' => 'resource-events.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\EventQuery' => 'resource-events.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Events' => 'resource-events.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Log' => 'resource-logs.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\LogBase' => 'resource-logs.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\LogQuery' => 'resource-logs.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Logs' => 'resource-logs.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PaginatorsClient' => 'client.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Post' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostBase' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostCommentsRelation' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostHandle' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostHasAuthor' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostHasComments' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostProjecting' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostProjection' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\PostQuery' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Posts' => 'resource-posts.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Tag' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\TagBase' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\TagQuery' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Tags' => 'resource-tags.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Writer' => 'resource-writers.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\WriterBase' => 'resource-writers.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\WriterQuery' => 'resource-writers.php',
        'haddowg\\JsonApiCodegen\\Tests\\Golden\\Paginators\\Writers' => 'resource-writers.php',
    ];

    if (isset($map[$class])) {
        require_once __DIR__ . '/' . $map[$class];
    }
});
