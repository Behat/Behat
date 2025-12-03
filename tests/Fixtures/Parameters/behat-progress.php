<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withFormatter(new ProgressFormatter())
    );
