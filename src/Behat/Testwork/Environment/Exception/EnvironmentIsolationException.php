<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Exception;

use Behat\Testwork\Environment\Environment;
use RuntimeException;

/**
 * Represents exception thrown during environment isolation process.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EnvironmentIsolationException extends RuntimeException implements EnvironmentException
{
    /**
     * Initializes exception.
     *
     * @param string      $message
     */
    public function __construct(
        $message,
        private readonly Environment $environment,
        private $subject = null,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns environment that caused exception.
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * Returns test subject that caused exception.
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
