<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->withSuite(
        (new Suite('suite-with-hyphens'))
            ->withPaths('features/little_kid.feature')
            ->withContexts('LittleKidContext')
    )
;

return (new Config())->withProfile($profile);
