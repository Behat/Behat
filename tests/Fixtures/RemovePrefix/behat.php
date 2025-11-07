<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('remove_prefix'))
        ->withPathOptions(removePrefix: [
            'features' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR,
            'features' . DIRECTORY_SEPARATOR,
        ])
    );
