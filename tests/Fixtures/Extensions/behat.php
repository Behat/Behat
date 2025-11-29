<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withExtension(new Extension('custom_extension.php', [
            'param1' => 'val1',
            'param2' => 'val2',
        ]))
        ->withSuite(new Suite('default', ['contexts' => ['FeatureContext']]))
    );
