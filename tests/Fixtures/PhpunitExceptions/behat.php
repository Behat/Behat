<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        new Profile('default')
    )
    ->withProfile(
        (new Profile('with-extension'))
          // Just stub an extension with the correct class name, we don't need (or want) to actually install it
          ->withExtension(new Extension(__DIR__.DIRECTORY_SEPARATOR.'StubPHPUnitAssertionsExtension.php', []))
    )
;
