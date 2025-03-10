<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS1.0' => true,
        'array_indentation' => true,
        'array_syntax' => true,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'function_declaration' => true, // overrides @PER-CS2.0
        'method_argument_space' => [ // overrides @PER-CS2.0
            'on_multiline' => 'ignore',
        ],
        'new_with_parentheses' => [
            'anonymous_class' => false,
        ],
        'single_line_empty_body' => false, // overrides @PER-CS2.0
        'single_space_around_construct' => [
            'constructs_followed_by_a_single_space' => [
                'abstract',
                'as',
                'case',
                'catch',
                'class',
                'const',
                'const_import',
                'do',
                'else',
                'elseif',
                'enum',
                'final',
                'finally',
                'for',
                'foreach',
                'function',
                'function_import',
                'if',
                'insteadof',
                'interface',
                'match',
                'named_argument',
                'namespace',
                'new',
                'private',
                'protected',
                'public',
                'readonly',
                'static',
                'switch',
                'trait',
                'try',
                'type_colon',
                'use',
                'use_lambda',
                'while',
            ],
            'constructs_preceded_by_a_single_space' => [
                'as',
                'else',
                'elseif',
                'use_lambda',
            ],
        ],
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
