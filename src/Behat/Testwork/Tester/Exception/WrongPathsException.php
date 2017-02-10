<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Exception;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Represents exception caused by a wrong paths argument.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WrongPathsException extends RuntimeException implements TesterException
{
    /**
     * @var string[]
     */
    private $paths;

    /**
     * Initializes exception.
     *
     * @param string          $message
     * @param string[]|string $paths
     */
    public function __construct($message, $paths)
    {
        parent::__construct($message);

        $this->paths = (array) $paths;
    }

    /**
     * Returns path that caused exception.
     *
     * @return string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Returns path that caused exception.
     *
     * @return string
     *
     * @deprecated
     */
    public function getPath()
    {
        return !empty($this->paths) ? reset($this->paths) : '';
    }
}
