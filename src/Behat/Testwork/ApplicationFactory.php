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
     */
    abstract protected function getName(): string;

    /**
     * Returns current application version.
     */
    abstract protected function getVersion(): string;

    /**
     * Returns list of extensions enabled by default.
     *
     * @return Extension[]
     */
    abstract protected function getDefaultExtensions(): array;

    /**
     * Returns the name of configuration environment variable.
     */
    abstract protected function getEnvironmentVariableName(): string;

    /**
     * Returns user config path.
     */
    abstract protected function getConfigPath(): ?string;

    /**
     * Creates application instance.
     */
    public function createApplication(): Application
    {
        $configurationLoader = $this->createConfigurationLoader();
        $extensionManager = $this->createExtensionManager();

        return new Application($this->getName(), $this->getVersion(), $configurationLoader, $extensionManager);
    }

    /**
     * Creates configuration loader.
     */
    protected function createConfigurationLoader(): ConfigurationLoader
    {
        return new ConfigurationLoader($this->getEnvironmentVariableName(), $this->getConfigPath());
    }

    /**
     * Creates extension manager.
     */
    protected function createExtensionManager(): ExtensionManager
    {
        return new ExtensionManager($this->getDefaultExtensions());
    }
}
