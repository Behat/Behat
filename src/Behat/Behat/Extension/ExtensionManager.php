<?php

namespace Behat\Behat\Extension;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use RuntimeException;

/**
 * Extensions manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExtensionManager
{
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var ExtensionInterface[]
     */
    private $extensions = array();
    /**
     * @var ExtensionInterface[string]
     */
    private $locatedExtensions = array();

    /**
     * Initializes manager.
     *
     * @param string $basePath base path where to search extension files
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Activate extension by its locator.
     *
     * @param string $locator phar file name, php file name, class name
     *
     * @return ExtensionInterface
     */
    public function activateExtension($locator)
    {
        $extension = $this->initialize($locator);

        return $this->extensions[$extension->getName()] = $extension;
    }

    /**
     * Returns specific extension by its name.
     *
     * @param string $name
     *
     * @return ExtensionInterface
     *
     * @throws RuntimeException
     */
    public function getExtension($name)
    {
        if (!isset($this->extensions[$name])) {
            throw new RuntimeException(
                sprintf('Extension "%s" has not been activated.', $name)
            );
        }

        return $this->extensions[$name];
    }

    /**
     * Returns all activated extensions.
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Returns activated extension classes.
     *
     * @return array
     */
    public function getExtensionClasses()
    {
        return array_unique(array_map('get_class', $this->extensions));
    }

    /**
     * Initializes extension by id.
     *
     * @param string $locator
     *
     * @return ExtensionInterface
     *
     * @throws RuntimeException
     */
    private function initialize($locator)
    {
        if (isset($this->locatedExtensions[$locator])) {
            return $this->locatedExtensions[$locator];
        }

        $extension = null;
        if (class_exists($locator)) {
            $extension = new $locator;
        } elseif (file_exists($this->basePath . DIRECTORY_SEPARATOR . $locator)) {
            $extension = require($this->basePath . DIRECTORY_SEPARATOR . $locator);
        } else {
            $extension = require($locator);
        }

        if (null === $extension) {
            throw new RuntimeException(sprintf(
                '"%s" extension could not be found.', $locator
            ));
        }
        if (!is_object($extension)) {
            throw new RuntimeException(sprintf(
                '"%s" extension could not be initialized.', $locator
            ));
        }
        if (!$extension instanceof ExtensionInterface) {
            throw new RuntimeException(sprintf(
                '"%s" extension class should implement ExtensionInterface.',
                get_class($extension)
            ));
        }

        return $this->locatedExtensions[$locator] = $extension;
    }
}
