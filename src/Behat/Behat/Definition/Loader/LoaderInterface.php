<?php

namespace Behat\Behat\Definition\Loader;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Definitions loader interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Load definitions from path.
     *
     * @param   string          $path       definitions path
     * @return  array                       array of Definitions & Transformations
     */
    public function load($path);
}
