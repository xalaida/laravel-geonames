<?php

$config = new PhpCsFixer\Config();

$config->setRules([
    '@PhpCsFixer:risky' => true,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
    'php_unit_test_annotation' => [
        'style' => 'annotation',
    ],
    'no_unused_imports' => true,
]);

$config->setRiskyAllowed(true);

return $config->setFinder(
    PhpCsFixer\Finder::create()->in(__DIR__)
);
