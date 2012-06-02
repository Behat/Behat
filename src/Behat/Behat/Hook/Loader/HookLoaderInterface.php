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
 * Hook loader interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface HookLoaderInterface
{
    /**
     * Loads definitions from provided resource.
     *
     * @param mixed $resource
     */
    public function load($resource);
}
