<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withExtension(new Extension('custom_extension.php'))
    );
