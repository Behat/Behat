<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        'concat_space' => false, // override Symfony
        'global_namespace_import' => [ //override Symfony
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'phpdoc_align' => false, //override Symfony
        'phpdoc_separation' => [ // override Symfony
            'groups' => [
                ['Annotation', 'NamedArgumentConstructor', 'Target'],
                ['Given', 'When', 'Then'],
                ...\PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer::OPTION_GROUPS_DEFAULT,
            ],
        ],
        'single_line_throw' => false, //override Symfony
        'yoda_style' => false, //override Symfony
    ])
    ->setFinder($finder);
