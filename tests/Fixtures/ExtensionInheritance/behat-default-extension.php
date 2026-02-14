<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(
                new Extension('custom_extension.php', [])
            )
    )
    ->withProfile(
        new Profile('custom_profile', ['extensions' => null])
    );
