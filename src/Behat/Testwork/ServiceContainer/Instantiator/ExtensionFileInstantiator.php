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

/**
 * Instantiate an extension by its filepath
 *
 * ```
 *   extensions:
 *       my_extension.php:
 *           # extension configuration
 * ```
 *
 * Note that the file *must* return an instance of the extension
 *
 * ```
 * class MyExtension
 * {
 *     // ...
 * }
 *
 * return new MyExtension();
 * ```
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class ExtensionFileInstantiator implements ExtensionInstantiator
{
    /**
     * @var string
     */
    private $extensionsPath;

    public function __construct($extensionsPath = null)
    {
        $this->extensionsPath = $extensionsPath;
    }

    /**
     * Sets path to directory in which manager will try to find extension files.
     *
     * @param null|string $path
     */
    public function setExtensionsPath($path)
    {
        $this->extensionsPath = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate($locator)
    {
        if (file_exists($locator)) {
            return require($locator);
        }

        if (file_exists($path = $this->extensionsPath . DIRECTORY_SEPARATOR . $locator)) {
            return require($path);
        }

        throw new ExtensionInitializationException(sprintf(
            '`%s` extension file could not be loaded.',
            $locator
        ), $locator);
    }
}
