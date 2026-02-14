<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('default'))
                    ->addContext('FeatureContext', [['param1' => 'val1', 'param2' => 'val1']])
            )
            ->withExtension(
                new Extension('custom_extension.php', [
                    'param1' => 'val1',
                    'param2' => 'val1',
                ])
            )
    )
    ->withProfile(
        (new Profile('custom_profile'))
            ->withSuite(
                (new Suite('default'))
                    ->addContext('FeatureContext', [['param1' => 'val1', 'param2' => 'val1']])
            )
            ->withExtension(
                new Extension('custom_extension.php', [
                    'param1' => 'val1',
                    'param2' => 'val1',
                ])
            )
    );
