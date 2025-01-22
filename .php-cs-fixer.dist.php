<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PSR1' => true,
        'blank_line_after_namespace' => true,
//        'braces_position' => true,
//        'class_definition' => true,
//        'constant_case' => true,
//        'control_structure_braces' => true,
//        'control_structure_continuation_position' => true,
//        'elseif' => true,
//        'function_declaration' => true,
//        'indentation_type' => true,
        'line_ending' => true,
//        'lowercase_keywords' => true,
        'method_argument_space' => ['attribute_placement' => 'ignore', 'on_multiline' => 'ensure_fully_multiline'],
//        'no_break_comment' => true,
        'no_closing_tag' => true,
//        'no_multiple_statements_per_line' => true,
        'no_space_around_double_colon' => true,
        'no_spaces_after_function_name' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'single_blank_line_at_eof' => true,
//        'single_class_element_per_statement' => ['elements' => ['property']],
//        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'single_space_around_construct' => ['constructs_followed_by_a_single_space' => ['abstract', 'as', 'case', 'catch', 'class', 'do', 'else', 'elseif', 'final', 'for', 'foreach', 'function', 'if', 'interface', 'namespace', 'private', 'protected', 'public', 'static', 'switch', 'trait', 'try', 'use_lambda', 'while'], 'constructs_preceded_by_a_single_space' => ['as', 'else', 'elseif', 'use_lambda']],
        'spaces_inside_parentheses' => true,
//        'statement_indentation' => true,
//        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
//        'visibility_required' => ['elements' => ['method', 'property']]
    ])
    ->setFinder($finder);
