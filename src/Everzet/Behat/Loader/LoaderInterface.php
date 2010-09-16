<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loader interface
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Loads files/scripts
     *
     * @param   string  $paths  paths to load from
     */
    public function load($paths);
}
