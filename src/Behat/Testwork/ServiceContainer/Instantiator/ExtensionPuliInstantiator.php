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

final class ExtensionPuliInstantiator implements ExtensionInstantiator
{
    const PULI_TYPE = 'behat/extension';

    private $extensions;

    /**
     * {@inheritDoc}
     */
    public function instantiate($locator)
    {
        $this->initializeExtensions();

        if (isset($this->extensions[$locator])) {
            return $this->extensions[$locator];
        }

        throw new ExtensionInitializationException(sprintf(
            '`%s` extension could not be loaded through Puli.',
            $locator
        ), $locator);
    }

    private function initializeExtensions()
    {
        if (null !== $this->extensions) {
            return $this->extensions;
        }

        if (!defined('PULI_FACTORY_CLASS') || !class_exists(PULI_FACTORY_CLASS)) {
            return array();
        }

        $this->extensions = array();
        $factoryClass = PULI_FACTORY_CLASS;

        $factory = new $factoryClass;
        $repository = $factory->createRepository();
        $discovery = $factory->createDiscovery($repository);

        foreach ($discovery->findBindings(self::PULI_TYPE) as $binding) {
            $this->extensions[$binding->getClassName()] = $binding->getClassName();

            if ($binding->hasParameterValue('name')) {
                $this->extensions[$binding->getParameterValue('name')] = $binding->getClassName();
            }
        }

        return $this->extensions;
    }
}
