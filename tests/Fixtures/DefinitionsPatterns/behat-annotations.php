<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('token_start'))
        ->withSuite(new Suite('default', [
            'contexts' => ['TokenStartAnnotations'],
        ]))
    )
    ->withProfile((new Profile('decimal_point'))
        ->withSuite(new Suite('default', [
            'contexts' => ['DecimalPointAnnotations'],
        ]))
    )
    ->withProfile((new Profile('string_with_point'))
        ->withSuite(new Suite('default', [
            'contexts' => ['StringWithPointAnnotations'],
        ]))
    )
    ->withProfile((new Profile('broken_regex'))
        ->withSuite(new Suite('default', [
            'contexts' => ['BrokenRegexAnnotations'],
        ]))
    )
    ->withProfile((new Profile('custom_regex'))
        ->withSuite(new Suite('default', [
            'contexts' => ['CustomRegexAnnotations'],
        ]))
    )
    ->withProfile((new Profile('decimal_number'))
        ->withSuite(new Suite('default', [
            'contexts' => ['DecimalNumberAnnotations'],
        ]))
    )
    ->withProfile((new Profile('empty_parameter'))
        ->withSuite(new Suite('default', [
            'contexts' => ['EmptyParameterAnnotations'],
        ]))
    )
    ->withProfile((new Profile('unix_path'))
        ->withSuite(new Suite('default', [
            'contexts' => ['UnixPathAnnotations'],
        ]))
    )
    ->withProfile((new Profile('negative_number'))
        ->withSuite(new Suite('default', [
            'contexts' => ['NegativeNumberAnnotations'],
        ]))
    );
