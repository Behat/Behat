<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite(new Suite('default', [
            'contexts' => ['ConstructorArgsContext'],
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))
    )
    ->withProfile((new Profile('constructor_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['ConstructorArgsContext'],
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))
    )
    ->withProfile((new Profile('mixed_constructor_args'))
        ->withSuite((new Suite('default', [
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))->addContext('MixedConstructorArgsContext', [
            'name' => 'Konstantin',
            's2' => '@Service2',
        ]))
    )
    ->withProfile((new Profile('null_args'))
        ->withSuite((new Suite('default', [
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))->addContext('NullArgsContext', [
            'name' => null,
        ]))
    )
    ->withProfile((new Profile('unregistered_constructor'))
        ->withSuite(new Suite('default', [
            'contexts' => ['UnregisteredConstructorContext'],
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))
    )
    ->withProfile((new Profile('step_definition_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['StepDefinitionArgsContext'],
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))
    )
    ->withProfile((new Profile('unregistered_step'))
        ->withSuite(new Suite('default', [
            'contexts' => ['UnregisteredStepContext'],
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))
    )
    ->withProfile((new Profile('transformation_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['TransformationArgsContext'],
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))
    )
    ->withProfile((new Profile('union_constructor_args'))
        ->withSuite(new Suite('default', [
            'contexts' => ['UnionConstructorArgsContext'],
            'autowire' => true,
            'services' => 'ServiceContainer',
        ]))
    );
