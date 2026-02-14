<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->import('behat.dist.php')
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('default'))
                    ->addContext('FeatureContext', [['param2' => 'val2']])
            )
            ->withExtension(
                new Extension('custom_extension.php', [
                    'param1' => 'val2',
                ])
            )
    )
    ->withProfile(
        (new Profile('custom_profile'))
            ->withSuite(
                (new Suite('default'))
                    ->addContext('FeatureContext', [['param2' => 'val2']])
            )
            ->withExtension(
                new Extension('custom_extension.php', [
                    'param1' => 'val2',
                ])
            )
    );
