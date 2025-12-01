<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite((new Suite('default', [
            'services' => [
                'parent_class' => [
                    'class' => 'ParentClass',
                ],
                'child_class' => [
                    'class' => 'ChildClass',
                ],
            ],
        ]))
            ->addContext('FirstContextTypehinted', ['@child_class', '@parent_class'])
        )
    );
