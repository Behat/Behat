<?php

namespace Behat\Behat\Hook\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Hooks loader interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Loads hooks from resource.
     * 
     * @param   string          $resource   resource path
     * @return  array                       array of hooks
     */
    function load($resource);
}
