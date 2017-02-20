<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer;

use Behat\Testwork\ServiceContainer\Exception\ExtensionInitializationException;

/**
 * Represents an instantiator for an Extension
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
interface ExtensionInstantiator
{
    /**
     * Instantiates extension from its locator.
     *
     * @param string $locator
     *
     * @return Extension
     *
     * @throws ExtensionInitializationException
     */
    public function instantiate($locator);
}
