<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Exception;

use Behat\Testwork\Suite\Suite;
use RuntimeException;

/**
 * Represents exception thrown during an environment build process.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EnvironmentBuildException extends RuntimeException implements EnvironmentException
{
    /**
     * @var Suite
     */
    private $suite;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param Suite  $suite
     */
    public function __construct($message, Suite $suite)
    {
        $this->suite = $suite;

        parent::__construct($message);
    }

    /**
     * Returns suite that caused exception.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }
}
