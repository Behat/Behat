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
    private $extensions = [];
    /**
     * @var Extension[string]
     */
    private $locatedExtensions = [];
    private $debugInformation = [
        'extensions_list' => [],
    ];

    /**
     * Initializes manager.
     *
     * @param Extension[] $extensions     List of default extensions
     * @param string|null $extensionsPath Base path where to search custom extension files
     */
    public function __construct(
        array $extensions,
        private $extensionsPath = null,
    ) {
        foreach ($extensions as $extension) {
            $this->extensions[$extension->getConfigKey()] = $extension;
        }
    }

    /**
     * Sets path to directory in which manager will try to find extension files.
     *
     * @param string|null $path
     */
    public function setExtensionsPath($path): void
    {
        $this->extensionsPath = $path;
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
     * @return Extension|null
     */
    public function getExtension($key)
    {
        return $this->extensions[$key] ?? null;
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
    public function initializeExtensions(): void
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
     * Attempts to guess full extension class from relative.
     *
     * @internal
     */
    public static function guessFullExtensionClassName(string $locator): string
    {
        $parts = explode('\\', $locator);
        $name = preg_replace('/Extension$/', '', end($parts)) . 'Extension';

        return $locator . '\\ServiceContainer\\' . $name;
    }

    /**
     * Initializes extension by id.
     *
     * @throws ExtensionInitializationException
     */
    private function initialize(string $locator): Extension
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
     * @throws ExtensionInitializationException
     */
    private function instantiateExtension(string $locator): mixed
    {
        if (class_exists($class = $locator)) {
            return new $class();
        }

        if (class_exists($class = self::guessFullExtensionClassName($locator))) {
            return new $class();
        }

        if (file_exists($locator)) {
            return require $locator;
        }

        if (file_exists($path = $this->extensionsPath . DIRECTORY_SEPARATOR . $locator)) {
            return require $path;
        }

        throw new ExtensionInitializationException(sprintf(
            '`%s` extension file or class could not be located.',
            $locator
        ), $locator);
    }

    /**
     * Validates extension instance.
     *
     * @throws ExtensionInitializationException
     */
    private function validateExtensionInstance(mixed $extension, string $locator): void
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
                $extension::class
            ), $locator);
        }
    }
}
