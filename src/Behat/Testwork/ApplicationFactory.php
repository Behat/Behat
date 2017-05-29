<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork;

use Behat\Testwork\Cli\Application;
use Behat\Testwork\ServiceContainer\Configuration\ConfigurationLoader;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ExtensionInstantiator;
use Behat\Testwork\ServiceContainer\Instantiator\ExtensionFileInstantiator;
use Behat\Testwork\ServiceContainer\Instantiator\ExtensionPuliInstantiator;
use Behat\Testwork\ServiceContainer\Instantiator\ExtensionClassInstantiator;

/**
 * Defines the way application is created.
 *
 * Extend and implement this class to create an entry point for your framework.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ApplicationFactory
{
    /**
     * Returns application name.
     *
     * @return string
     */
    abstract protected function getName();

    /**
     * Returns current application version.
     *
     * @return string
     */
    abstract protected function getVersion();

    /**
     * Returns list of extensions enabled by default.
     *
     * @return Extension[]
     */
    abstract protected function getDefaultExtensions();

    /**
     * Returns the name of configuration environment variable.
     *
     * @return string
     */
    abstract protected function getEnvironmentVariableName();

    /**
     * Returns user config path.
     *
     * @return null|string
     */
    abstract protected function getConfigPath();

    /**
     * Returns the extension instantiators
     *
     * @return ExtensionInstantiator[]
     */
    protected function getDefaultInstantiators()
    {
        $instantiators = array(
            new ExtensionClassInstantiator(),
            new ExtensionFileInstantiator(),
        );

        if (defined('PULI_FACTORY_CLASS') && class_exists(PULI_FACTORY_CLASS)) {
            $instantiators[] = new ExtensionPuliInstantiator();
        }

        return $instantiators;
    }

    /**
     * Creates application instance.
     *
     * @return Application
     */
    public function createApplication()
    {
        $configurationLoader = $this->createConfigurationLoader();
        $extensionManager = $this->createExtensionManager();

        return new Application($this->getName(), $this->getVersion(), $configurationLoader, $extensionManager);
    }

    /**
     * Creates configuration loader.
     *
     * @return ConfigurationLoader
     */
    protected function createConfigurationLoader()
    {
        return new ConfigurationLoader($this->getEnvironmentVariableName(), $this->getConfigPath());
    }

    /**
     * Creates extension manager.
     *
     * @return ExtensionManager
     */
    protected function createExtensionManager()
    {
        return new ExtensionManager($this->getDefaultExtensions(), null, $this->getDefaultInstantiators());
    }
}
