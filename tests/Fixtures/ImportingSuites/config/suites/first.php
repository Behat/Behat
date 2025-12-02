<?php

declare(strict_types=1);

$config = new Behat\Config\Config([
    'default' => [
        'suites' => [
            'first' => [
                'contexts' => ['FirstContext'],
            ],
        ],
    ],
]);

return $config;
