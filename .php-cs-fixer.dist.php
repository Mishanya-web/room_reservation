<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('migrations')
    ->exclude('data')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,

        // Базовые правила
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'single_quote' => true,
        'line_ending' => true,
        'encoding' => true,

        // Строгая типизация
        'declare_strict_types' => true,

        // Импорт из глобального пространства
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],

        // Пробелы и форматирование
        'no_extra_blank_lines' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_whitespace_in_blank_line' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,

        // Кавычки
        'single_quote' => true,

        // PHP-док
        'phpdoc_no_package' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,

        // Прочее
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'single'],
    ])
    ->setFinder($finder);
