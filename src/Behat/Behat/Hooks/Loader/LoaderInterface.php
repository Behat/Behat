<?php

namespace Behat\Behat\Hooks\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Hooks Loader Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Load hooks from file. 
     * 
     * @param   string          $path       plain php file path
     * @return  array                       array of hooks
     */
    function load($path);
}
