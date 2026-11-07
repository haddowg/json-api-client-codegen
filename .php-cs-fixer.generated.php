<?php

declare(strict_types=1);

/*
 * The generated output is held to the same standard as the codegen itself. It targets PHP 8.4,
 * so this config runs on 8.4 only (CI's coding-standards job does). `composer cs-check-generated`
 * writes the output to build/generated-output first.
 */
$config = require __DIR__ . '/.php-cs-fixer.dist.php';

return $config
    ->setFinder(PhpCsFixer\Finder::create()->in([__DIR__ . '/build/generated-output/generated', __DIR__ . '/build/generated-output/paginators', __DIR__ . '/build/generated-output/witness'])->name('*.php'))
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.generated.cache');
