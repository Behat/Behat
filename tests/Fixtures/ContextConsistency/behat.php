<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContext'],
        ]))
    )
    ->withProfile((new Profile('params'))
        ->withSuite((new Suite('default', []))->addContext('FeatureContext', [
            'parameter1' => 'val_one',
            'parameter2' => [
                'everzet' => 'behat_admin',
                'avalanche123' => 'behat_admin',
            ],
        ]))
    )
    ->withProfile((new Profile('params_optional'))
        ->withSuite((new Suite('default', []))->addContext('FeatureContext', [
            'parameter1' => 'val_one',
        ]))
    )
    ->withProfile((new Profile('custom_context'))
        ->withSuite(new Suite('default', [
            'contexts' => ['CustomContext'],
        ]))
    )
    ->withProfile((new Profile('array_arguments'))
        ->withSuite((new Suite('default', []))->addContext('FirstContext', [
            ['foo', 'bar'],
        ]))
    )
    ->withProfile((new Profile('empty_contexts'))
        ->withSuite(new Suite('first', [
            'contexts' => [],
        ]))
    )
    ->withProfile((new Profile('custom_context_with_hook'))
        ->withSuite(new Suite('first', [
            'contexts' => ['CustomContextWithHook'],
        ]))
    )
    ->withProfile((new Profile('single_context'))
        ->withSuite(new Suite('default', [
            'contexts' => 'UnexistentContext',
        ]))
    )
    ->withProfile((new Profile('unexisting_context'))
        ->withSuite(new Suite('default', [
            'contexts' => ['UnexistentContext'],
        ]))
    )
    ->withProfile((new Profile('unexisting_param'))
        ->withSuite((new Suite('default', []))->addContext('FeatureContext', [
            'unexistingParam' => 'value',
        ]))
    );
