<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\NameFilter;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withFilter(new TagFilter('~@wip'))
    )
    ->withProfile(
        (new Profile('wip'))
            ->withFilter(new NameFilter('A simple feature'))
    )
;
