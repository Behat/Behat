<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\NameFilter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withFilter(new NameFilter('simple feature'))
    )
;
