<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer\Instantiator;

use Behat\Testwork\ServiceContainer\ExtensionInstantiator;
use Behat\Testwork\ServiceContainer\Exception\ExtensionInitializationException;

final class ExtensionClassInstantiator implements ExtensionInstantiator
{
    /**
     * {@inheritDoc}
     */
    public function instantiate($locator)
    {
        if (class_exists($class = $locator)) {
            return new $class;
        }

        if (class_exists($class = $this->getFullExtensionClass($locator))) {
            return new $class;
        }

        throw new ExtensionInitializationException(sprintf(
            '`%s` extension class could not be instantiated.',
            $locator
        ), $locator);
    }

    /**
     * Attempts to guess full extension class from relative.
     *
     * @param string $locator
     *
     * @return string
     */
    private function getFullExtensionClass($locator)
    {
        $parts = explode('\\', $locator);
        $name = preg_replace('/Extension$/', '', end($parts)) . 'Extension';

        return sprintf('%s\\ServiceContainer\\%s', $locator, $name);
    }
}
