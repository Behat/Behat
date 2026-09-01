<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagExpressionFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withFilter(new TagExpressionFilter('@fast and not @slow'))
    )
;

return (new Config())->withProfile($profile);
