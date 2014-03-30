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
     * @var string
     */
    private $path;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $path
     */
    public function __construct($message, $path)
    {
        parent::__construct($message);

        $this->path = $path;
    }

    /**
     * Returns path that caused exception.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
