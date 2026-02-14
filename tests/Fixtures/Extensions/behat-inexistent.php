<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(new Extension('inexistent_extension'))
            ->withSuite(
                (new Suite('default'))
                    ->withContexts('InexistentExtensionContext')
            )
    );
