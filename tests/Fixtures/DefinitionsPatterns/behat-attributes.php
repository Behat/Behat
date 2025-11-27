<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('token_start'))
        ->withSuite(new Suite('default', [
            'contexts' => ['TokenStartAttributes'],
        ]))
    )
    ->withProfile((new Profile('decimal_point'))
        ->withSuite(new Suite('default', [
            'contexts' => ['DecimalPointAttributes'],
        ]))
    )
    ->withProfile((new Profile('string_with_point'))
        ->withSuite(new Suite('default', [
            'contexts' => ['StringWithPointAttributes'],
        ]))
    )
    ->withProfile((new Profile('broken_regex'))
        ->withSuite(new Suite('default', [
            'contexts' => ['BrokenRegexAttributes'],
        ]))
    )
    ->withProfile((new Profile('custom_regex'))
        ->withSuite(new Suite('default', [
            'contexts' => ['CustomRegexAttributes'],
        ]))
    )
    ->withProfile((new Profile('decimal_number'))
        ->withSuite(new Suite('default', [
            'contexts' => ['DecimalNumberAttributes'],
        ]))
    )
    ->withProfile((new Profile('empty_parameter'))
        ->withSuite(new Suite('default', [
            'contexts' => ['EmptyParameterAttributes'],
        ]))
    )
    ->withProfile((new Profile('unix_path'))
        ->withSuite(new Suite('default', [
            'contexts' => ['UnixPathAttributes'],
        ]))
    )
    ->withProfile((new Profile('negative_number'))
        ->withSuite(new Suite('default', [
            'contexts' => ['NegativeNumberAttributes'],
        ]))
    );
