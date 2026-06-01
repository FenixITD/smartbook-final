<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
    ->in([
        __DIR__.'/app',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->exclude('Dto');

return (new Config)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ->setFinder($finder)
    ->setRules([
        // ─── Rule sets ────────────────────────────────────────────────────────
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP82Migration' => true,
        '@PHP82Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // ─── Strict types ─────────────────────────────────────────────────────
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,

        // ─── Imports ──────────────────────────────────────────────────────────
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
        'no_unused_imports' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'fully_qualified_strict_types' => true,

        // ─── Arrays ───────────────────────────────────────────────────────────
        'array_syntax' => ['syntax' => 'short'],
        'trim_array_spaces' => true,
        'array_indentation' => true,
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
        'normalize_index_brace' => true,

        // ─── Classes & OOP ────────────────────────────────────────────────────
        'final_class' => false, // Controllers & models are not final by convention
        'self_accessor' => true,
        'self_static_accessor' => true,
        'protected_to_private' => true,
        'no_null_property_initialization' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'construct',
                'destruct',
                'magic',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],

        // ─── PHPDoc ───────────────────────────────────────────────────────────
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_no_empty_return' => true,
        'phpdoc_scalar' => true,
        'phpdoc_to_comment' => false, // Allow /** @var */ inside methods
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => false, 'allow_unused_params' => false, 'remove_inheritdoc' => false],
        'general_phpdoc_tag_rename' => true,
        'phpdoc_tag_casing' => true,

        // ─── Type-hints ───────────────────────────────────────────────────────
        'void_return' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'nullable_type_declaration_for_default_null_value' => true,
        'nullable_type_declaration' => ['syntax' => 'union'],

        // ─── Strings ──────────────────────────────────────────────────────────
        'single_quote' => ['strings_containing_single_quote_chars' => false],
        'explicit_string_variable' => true,
        'simple_to_complex_string_variable' => true,
        'no_binary_string' => true,
        'string_implicit_backslashes' => true,

        // ─── Control flow ─────────────────────────────────────────────────────
        'no_useless_else' => true,
        'no_useless_return' => true,
        'simplified_if_return' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],

        // ─── Operators ────────────────────────────────────────────────────────
        'logical_operators' => true,   // and/or → &&/||
        'operator_linebreak' => ['only_booleans' => false, 'position' => 'beginning'],
        'standardize_not_equals' => true,   // <> → !=
        'ternary_to_null_coalescing' => true,
        'assign_null_coalescing_to_coalesce_equal' => true,

        // ─── Functions ────────────────────────────────────────────────────────
        'static_lambda' => true,
        'use_arrow_functions' => true,
        'no_unreachable_default_argument_value' => true,
        'regular_callable_call' => true,
        'implode_call' => true,

        // ─── Casts ────────────────────────────────────────────────────────────
        'modernize_types_casting' => true,
        'no_short_bool_cast' => true,

        // ─── Comments ─────────────────────────────────────────────────────────
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'single_line_comment_spacing' => true,

        // ─── Whitespace & blank lines ─────────────────────────────────────────
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'default', 'do', 'exit', 'for', 'foreach', 'goto', 'if', 'include', 'include_once', 'phpdoc', 'require', 'require_once', 'return', 'switch', 'throw', 'try', 'while', 'yield', 'yield_from'],
        ],
        'no_extra_blank_lines' => [
            'tokens' => ['attribute', 'break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use'],
        ],
        'class_attributes_separation' => [
            'elements' => ['const' => 'one', 'method' => 'one', 'property' => 'one', 'trait_import' => 'none', 'case' => 'none'],
        ],

        // ─── Semicolons ───────────────────────────────────────────────────────
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true,
    ]);
