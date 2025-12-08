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
     * Initializes exception.
     *
     * @param string $message
     */
    public function __construct(
        $message,
        private readonly Suite $suite,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns suite that caused exception.
     */
    public function getSuite(): Suite
    {
        return $this->suite;
    }
}
