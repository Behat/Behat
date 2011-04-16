#!/usr/bin/env php
<?php

/*
 * Behat
 *
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('BEHAT_PHP_BIN_PATH',    '/usr/bin/env php');
define('BEHAT_BIN_PATH',        __FILE__);
define('BEHAT_VERSION',         'DEV');

if (is_file(__DIR__ . '/../autoload.php')) {
    require_once __DIR__ . '/../autoload.php';
} elseif (is_file(__DIR__ . '/../autoload.php.dist')) {
    require_once __DIR__ . '/../autoload.php.dist';
} else {
    require_once 'behat/autoload.php.dist';
}

// Internal encoding to utf8
mb_internal_encoding('utf8');

$app = new Behat\Behat\Console\BehatApplication(BEHAT_VERSION);
$app->run();
