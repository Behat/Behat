#!/usr/bin/env php
<?php

Phar::mapPhar('behat');

// Load the autoloader specific to the project Behat is being used in so
// any installed behat extensions and feature contexts can be autoloaded
if (is_file($projectAutoloader = behatPhar_getProjectDir() . '/vendor/autoload.php')) {
    var_dump('Require '.$projectAutoloader);
    require $projectAutoloader;
}

require 'phar://behat/vendor/autoload.php';

define('BEHAT_BIN_PATH', Phar::running(false));

/**
 * Function based on Symfony\Component\HttpKernel\Kernel::getProjectDir()
 */
function behatPhar_getProjectDir()
{
    $r = new \ReflectionFunction('behatPhar_getProjectDir');
    $dir = $rootDir = \dirname($r->getFileName());
    while (!file_exists($dir.'/composer.json')) {
        if ($dir === \dirname($dir)) {
            return $rootDir;
        }

        $dir = \dirname($dir);
    }

    return $dir;
}

$factory = new \Behat\Behat\ApplicationFactory();
$factory->createApplication()->run();

__HALT_COMPILER(); ?>
