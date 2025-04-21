<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS2.0' => true,

//        'backtick_to_shell_exec' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'return',
            ],
        ],
//        'braces_position' => [
//            'allow_single_line_anonymous_functions' => true,
//            'allow_single_line_empty_anonymous_classes' => true,
//        ],
//        'class_attributes_separation' => [
//            'elements' => [
//                'method' => 'one',
//            ],
//        ],
//        'class_definition' => [
//            'single_line' => true,
//        ],
//        'class_reference_name_casing' => true,
//        'declare_parentheses' => true,
//        'echo_tag_syntax' => true,
//        'fully_qualified_strict_types' => true,
//        'include' => true,
//        'increment_style' => true,
//        'integer_literal_case' => true,
//        'magic_constant_casing' => true,
//        'magic_method_casing' => true,
//        'native_function_casing' => true,
//        'native_type_declaration_casing' => true,
//        'no_alias_language_construct_call' => true,
//        'no_alternative_syntax' => true,
//        'no_binary_string' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
//        'no_empty_statement' => true,
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
//        'no_mixed_echo_print' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
//        'no_null_property_initialization' => true,
//        'no_short_bool_cast' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_hidden_params' => true,
            'remove_inheritdoc' => true,
        ],
//        'no_trailing_comma_in_singleline' => true,
//        'no_unneeded_braces' => [
//            'namespaces' => true,
//        ],
//        'no_unneeded_control_parentheses' => [
//            'statements' => [
//                'break',
//                'clone',
//                'continue',
//                'echo_print',
//                'others',
//                'return',
//                'switch_case',
//                'yield',
//                'yield_from',
//            ],
//        ],
//        'no_unset_cast' => true,
        'no_unused_imports' => true,
//        'no_useless_concat_operator' => true,
//        'no_useless_nullsafe_operator' => true,
//        'normalize_index_brace' => true,
//        'nullable_type_declaration' => true,
//        'nullable_type_declaration_for_default_null_value' => true,
//        'operator_linebreak' => [
//            'only_booleans' => true,
//        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
//        'ordered_types' => [
//            'null_adjustment' => 'always_last',
//            'sort_algorithm' => 'none',
//        ],
//        'php_unit_fqcn_annotation' => true,
//        'php_unit_method_casing' => true,
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
//        'semicolon_after_instruction' => true,
//        'simple_to_complex_string_variable' => true,
//        'single_class_element_per_statement' => true,
//        'single_line_throw' => true,
//        'single_quote' => true,
        'single_space_around_construct' => true,
//        'standardize_increment' => true,
//        'standardize_not_equals' => true,
//        'statement_indentation' => [
//            'stick_comment_to_next_continuous_control_statement' => true,
//        ],
//        'switch_continue_to_break' => true,
        'type_declaration_spaces' => true,
        'unary_operator_spaces' => true,
//        'yoda_style' => true,

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
