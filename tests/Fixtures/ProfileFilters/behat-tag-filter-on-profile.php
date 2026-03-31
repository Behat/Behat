<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withGherkinOptions((new GherkinOptions())
                ->withFilter(new TagFilter('tag2'))
            )
    )
;
