<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loader Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Load something.
     *
     * @param   string  $paths  paths to load from
     */
    public function load($paths);
}
