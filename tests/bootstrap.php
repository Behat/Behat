<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespace('Gherkin', realpath(__DIR__ . '/../src'));
$loader->register();
