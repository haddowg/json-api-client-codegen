<?php

declare(strict_types=1);

/*
 * The generated output is held to the same standard as the codegen itself. It targets PHP 8.4,
 * so this config runs on 8.4 only (CI's coding-standards job does).
 */
$config = require __DIR__ . '/.php-cs-fixer.dist.php';

return $config
    ->setFinder(PhpCsFixer\Finder::create()->in([__DIR__ . '/tests/golden/generated', __DIR__ . '/tests/golden/paginators'])->name('*.php'))
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.generated.cache');
