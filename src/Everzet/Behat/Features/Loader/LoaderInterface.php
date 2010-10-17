<?php

namespace Everzet\Behat\Features\Loader;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Feature Loader Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Load feature from specified path.
     *
     * @param   string                              $paths  features path(s)
     * 
     * @return  Everzet\Gherkin\Node\FeatureNode            feature node
     */
    public function load($path);
}

