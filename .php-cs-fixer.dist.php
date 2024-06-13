<?php

declare(strict_types=1);

try {
    $composer = json_decode(
        file_get_contents(__DIR__ . '/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    echo "Unable to parse composer.json file with JSON error [{$exception->getFile()}].\n";

    exit(1);
}

$projectName = $composer['name'];

$license = file_get_contents(__DIR__.'/LICENSE');

$headerComment = <<<COMMENT
This file is part of the {$projectName} package.

{$license}
COMMENT;

$finder = PhpCsFixer\Finder::create()
    ->notPath('vendor')
    ->notName('test.php')
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR12' => true,
        'binary_operator_spaces' => [
            'operators' => ['=>' => null],
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'not_operator_with_successor_space' => true,
        'header_comment' => [
            'header' => $headerComment,
            'separate' => 'both',
            'location' => 'after_open',
            'comment_type' => 'PHPDoc',
        ],
        'linebreak_after_opening_tag' => true,
        'mb_str_functions' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'length',
        ],
        'php_unit_strict' => true,
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => true,
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'yoda_style' => false,
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => true,
        ],
        'native_function_invocation'=> false,
        'native_constant_invocation' => false,
        'declare_strict_types' => true,
        'phpdoc_to_comment' => false,
        'fopen_flags' => [
            'b_mode' => true,
        ],
        'php_unit_method_casing' => [
            'case' => 'snake_case',
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'nullable_type_declaration_for_default_null_value' => true,
        'get_class_to_class_keyword' => false,
        'fully_qualified_strict_types' => false,
    ])
    ->setFinder($finder);
