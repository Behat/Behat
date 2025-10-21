<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('do-not-ignore-any'))
        ->withTesterOptions((new TesterOptions())
            ->withErrorReporting(E_ALL)))
    ->withProfile((new Profile('ignore-all-but-error'))
        ->withTesterOptions((new TesterOptions())
            ->withErrorReporting(E_ALL & ~(E_WARNING | E_NOTICE | E_DEPRECATED))))
;
