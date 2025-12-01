<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withExtension(new Extension('MyExtension.php'))
        ->withSuite((new Suite('default', ['services' => '@my_extension.container']))
            ->addContext('FirstContext', ['@shared_service'])
            ->addContext('SecondContext', ['@shared_service'])
        )
    );
