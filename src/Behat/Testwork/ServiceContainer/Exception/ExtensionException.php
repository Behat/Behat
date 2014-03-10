<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer\Exception;

use RuntimeException;

/**
 * Extension exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExtensionException extends RuntimeException implements ServiceContainerException
{
    /**
     * @var string
     */
    private $extensionName;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $extensionName
     */
    public function __construct($message, $extensionName)
    {
        $this->extensionName = $extensionName;

        parent::__construct($message);
    }

    /**
     * Returns name of the extension that caused exception.
     *
     * @return string
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }
}
