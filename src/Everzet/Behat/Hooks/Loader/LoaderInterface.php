<?php

namespace Everzet\Behat\Hooks\Loader;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
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
    public function load($path);
}

