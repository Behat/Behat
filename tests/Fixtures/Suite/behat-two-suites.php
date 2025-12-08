<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
  ->withSuite(
      (new Suite('first', ['contexts' => ['FirstContext']]))
        ->withPaths('%paths.base%/features/some.feature')
  )
  ->withSuite(
      (new Suite('second'))
        ->withContexts('SecondContext')
        ->withPaths('%paths.base%/features/some.feature')
  )
;

return (new Config())->withProfile($profile);
