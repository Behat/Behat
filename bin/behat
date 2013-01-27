#!/usr/bin/env php
<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('BEHAT_PHP_BIN_PATH', getenv('PHP_PEAR_PHP_BIN') ?: '/usr/bin/env php');
define('BEHAT_BIN_PATH',     __FILE__);
define('BEHAT_VERSION',      'DEV');

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
    die(
        'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL
    );
}

$app = new Behat\Behat\Console\BehatApplication(BEHAT_VERSION);
$app->run();
