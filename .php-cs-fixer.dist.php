<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;

$finder = (new Finder())
    ->in(__DIR__)
;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
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
                ...PhpdocSeparationFixer::OPTION_GROUPS_DEFAULT,
            ],
        ],
        'single_line_throw' => false, //override Symfony
        'yoda_style' => false, //override Symfony
        'phpdoc_to_comment' => [ // Keep as a phpdoc if it contains the `@var` annotation
            'ignored_tags' => ['var'],
        ],
    ])
    ->setFinder($finder);
