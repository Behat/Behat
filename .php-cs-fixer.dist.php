<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS2.0' => true,

        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'return',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
            ],
        ],
        'class_reference_name_casing' => true,
        'fully_qualified_strict_types' => true,
        'include' => true,
        'increment_style' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'attribute',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ],
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_null_property_initialization' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_hidden_params' => true,
            'remove_inheritdoc' => true,
        ],
        'no_unneeded_control_parentheses' => [
            'statements' => [
                'break',
                'clone',
                'continue',
                'echo_print',
                'others',
                'return',
                'switch_case',
                'yield',
                'yield_from',
            ],
        ],
        'no_unused_imports' => true,
        'operator_linebreak' => [
            'only_booleans' => true,
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
//        'phpdoc_align' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => [
            'order' => [
                'param',
                'return',
                'throws',
            ],
        ],
        'phpdoc_scalar' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_type' => [
            'tags' => [
                'inheritDoc' => 'inline',
            ],
        ],
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'single_quote' => true,
        'standardize_increment' => true,
        'type_declaration_spaces' => true,
        'unary_operator_spaces' => true,

        'concat_space' => false, // override Symfony
        'phpdoc_separation' => [ // override Symfony
            'groups' => [
                ['Annotation', 'NamedArgumentConstructor', 'Target'],
                ['Given', 'When', 'Then'],
                ...\PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer::OPTION_GROUPS_DEFAULT,
            ],
        ],
        'global_namespace_import' => [ //override Symfony
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'single_line_throw' => false, //override Symfony
        'yoda_style' => false, //override Symfony

        'function_declaration' => true, // overrides @PER-CS2.0
        'method_argument_space' => [ // overrides @PER-CS2.0
            'on_multiline' => 'ignore',
        ],
        'single_line_empty_body' => false, // overrides @PER-CS2.0
        'trailing_comma_in_multiline' => [ // overrides @PER-CS2.0
            'after_heredoc' => true,
            'elements' => [
                'array_destructuring',
                'arrays',
                'match',
                'parameters',
            ],
        ],
    ])
    ->setFinder($finder);
