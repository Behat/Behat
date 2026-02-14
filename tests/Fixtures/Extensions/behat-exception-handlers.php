<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(new Extension('custom_handlers_extension.php'))
            ->withSuite(
                (new Suite('default'))
                    ->withContexts('ExceptionHandlerContext')
            )
    );
