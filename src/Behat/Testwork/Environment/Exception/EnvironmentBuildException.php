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
 * Test environment build exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EnvironmentBuildException extends RuntimeException implements EnvironmentException
{
    /**
     * @var Suite
     */
    private $suite;
    /**
     * @var mixed
     */
    private $testSubject;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param Suite  $suite
     * @param mixed  $subject
     */
    public function __construct($message, Suite $suite, $subject)
    {
        $this->suite = $suite;
        $this->testSubject = $subject;

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

    /**
     * Returns test subject that caused exception.
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->testSubject;
    }
}
