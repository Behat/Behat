<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Profile;

$profile = (new Profile('pretty_without_paths'))
    ->disableFormatter(ProgressFormatter::NAME)
    ->withFormatter(new PrettyFormatter(paths: false))
;

return (new Config())->withProfile($profile);
