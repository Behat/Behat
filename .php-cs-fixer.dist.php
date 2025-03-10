<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS2.0' => true,
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
