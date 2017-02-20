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
 * Manages both default and 3rd-party extensions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExtensionManager
{
    /**
     * @var Extension[]
     */
    private $extensions = array();
    /**
     * @var Extension[string]
     */
    private $locatedExtensions = array();
    private $debugInformation = array(
        'extensions_list' => array()
    );
    /**
     * @var ExtensionInstantiator[]
     */
    private $instantiators = array();

    /**
     * Initializes manager.
     *
     * @param Extension[]             $extensions    List of default extensions
     * @param ExtensionInstantiator[] $instantiators Extension instantiators to use
     */
    public function __construct(array $extensions, array $instantiators)
    {
        foreach ($extensions as $extension) {
            $this->extensions[$extension->getConfigKey()] = $extension;
        }

        $this->instantiators = $instantiators;
    }

    /**
     * Activate extension by its locator.
     *
     * @param string $locator phar file name, php file name, class name
     *
     * @return Extension
     */
    public function activateExtension($locator)
    {
        $extension = $this->initialize($locator);

        $this->debugInformation['extensions_list'][] = $extension->getConfigKey();

        return $this->extensions[$extension->getConfigKey()] = $extension;
    }

    /**
     * Returns specific extension by its name.
     *
     * @param string $key
     *
     * @return Extension
     */
    public function getExtension($key)
    {
        return isset($this->extensions[$key]) ? $this->extensions[$key] : null;
    }

    /**
     * Returns all available extensions.
     *
     * @return Extension[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Returns activated extension names.
     *
     * @return array
     */
    public function getExtensionClasses()
    {
        return array_map('get_class', array_values($this->extensions));
    }

    /**
     * Initializes all activated and predefined extensions.
     */
    public function initializeExtensions()
    {
        foreach ($this->extensions as $extension) {
            $extension->initialize($this);
        }
    }

    /**
     * Returns array with extensions debug information.
     *
     * @return array
     */
    public function debugInformation()
    {
        return $this->debugInformation;
    }

    /**
     * Initializes extension by id.
     *
     * @param string $locator
     *
     * @return Extension
     *
     * @throws ExtensionInitializationException
     */
    private function initialize($locator)
    {
        if (isset($this->locatedExtensions[$locator])) {
            return $this->locatedExtensions[$locator];
        }

        $extension = $this->instantiateExtension($locator);
        $this->validateExtensionInstance($extension, $locator);

        return $this->locatedExtensions[$locator] = $extension;
    }

    /**
     * Instantiates extension from its locator.
     *
     * @param string $locator
     *
     * @return Extension
     *
     * @throws ExtensionInitializationException
     */
    private function instantiateExtension($locator)
    {
        foreach ($this->instantiators as $instantiator) {
            try {
                return $instantiator->instantiate($locator);
            } catch (ExtensionInitializationException $e) {
                // ignored if the instantiator does not support the locator
            }
        }

        throw new ExtensionInitializationException(sprintf(
            '`%s` extension file or class could not be located.',
            $locator
        ), $locator);
    }

    /**
     * Validates extension instance.
     *
     * @param Extension $extension
     * @param string    $locator
     *
     * @throws ExtensionInitializationException
     */
    private function validateExtensionInstance($extension, $locator)
    {
        if (null === $extension) {
            throw new ExtensionInitializationException(sprintf(
                '`%s` extension could not be found.',
                $locator
            ), $locator);
        }

        if (!is_object($extension)) {
            throw new ExtensionInitializationException(sprintf(
                '`%s` extension could not be initialized.',
                $locator
            ), $locator);
        }

        if (!$extension instanceof Extension) {
            throw new ExtensionInitializationException(sprintf(
                '`%s` extension class should implement Testwork Extension interface.',
                get_class($extension)
            ), $locator);
        }
    }
}
