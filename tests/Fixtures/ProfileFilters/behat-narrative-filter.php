<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\NarrativeFilter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withFilter(new NarrativeFilter('/As a (?:second|third) user/'))
    )
;
