<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->import('pretty.php')
    ->withProfile(new Profile('default', [
        'formatters' => [
            'pretty' => false,
            'progress' => null,
        ],
    ]))
    ->withProfile(new Profile('pretty_without_paths', [
        'formatters' => [
            'progress' => false,
            'pretty' => [
                'paths' => false,
            ],
        ],
    ]))
;
