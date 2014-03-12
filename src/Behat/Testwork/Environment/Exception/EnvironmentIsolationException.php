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
     * @var Environment
     */
    private $environment;
    /**
     * @var mixed
     */
    private $subject;

    /**
     * Initializes exception.
     *
     * @param string      $message
     * @param Environment $environment
     * @param mixed       $testSubject
     */
    public function __construct($message, Environment $environment, $testSubject = null)
    {
        $this->environment = $environment;
        $this->subject = $testSubject;

        parent::__construct($message);
    }

    /**
     * Returns environment that caused exception.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Returns test subject that caused exception.
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
