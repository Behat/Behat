<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$firstSuite = (new Suite('first'))
  ->withPaths('%paths.base%/features/first')
  ->withContexts('FirstContext')
;

$secondSuite = (new Suite('second'))
  ->withPaths('%paths.base%/features/second')
  ->withContexts('SecondContext')
;

$profile = (new Profile('default'))
  ->withSuite($firstSuite)
  ->withSuite($secondSuite)
;

return (new Config())->withProfile($profile);
