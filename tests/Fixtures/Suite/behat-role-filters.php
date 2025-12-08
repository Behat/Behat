<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\RoleFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
  ->withSuite(
      (new Suite('little_kid'))
        ->withContexts(LittleKidContext::class)
        ->withPaths('%paths.base%/features/little_kid.feature')
        ->withFilter(new RoleFilter('little kid'))
  )
  ->withSuite(
      (new Suite('big_brother'))
        ->withContexts(BigBrotherContext::class)
        ->withPaths('%paths.base%/features/big_brother.feature')
        ->withFilter(new RoleFilter('big brother'))
  )
;

return (new Config())->withProfile($profile);
