<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../vendor/symfony/src/Symfony/Foundation/UniversalClassLoader.php';

$loader = new \Symfony\Foundation\UniversalClassLoader();
$loader->registerNamespace('Symfony', realpath(__DIR__ . '/../vendor/symfony/src'));
$loader->registerNamespace('Zend', realpath(__DIR__ . '/../vendor/Zend/library'));
$loader->registerNamespace('Goutte', realpath(__DIR__ . '/../vendor/Goutte/src'));

$loader->registerNamespace('Gherkin', realpath(__DIR__ . '/../src'));
$loader->registerNamespace('Behat', realpath(__DIR__ . '/../src'));

$loader->register();
