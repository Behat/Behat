<?php

declare(strict_types=1);

$config = new Behat\Config\Config();
$config->import(['config/suites/first.php', 'config/suites/second.php']);

return $config;
