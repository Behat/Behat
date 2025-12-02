<?php

declare(strict_types=1);

$config = new Behat\Config\Config([
    'default' => [
        'suites' => [
            'second' => [
                'contexts' => ['SecondContext'],
            ],
        ],
    ],
]);

return $config;
