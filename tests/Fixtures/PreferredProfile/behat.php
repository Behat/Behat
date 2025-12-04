<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Profile;

$defaultProfile = new Profile('default');

$progressProfile = (new Profile('progress'))
    ->disableFormatter(PrettyFormatter::NAME)
    ->withFormatter(new ProgressFormatter())
;

return (new Config())
    ->withProfile($defaultProfile)
    ->withProfile($progressProfile)
    ->withPreferredProfile('progress')
    ->import('pretty.php')
;
