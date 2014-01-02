<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject\Exception;

use Behat\Testwork\Suite\Suite;
use RuntimeException;

/**
 * Testwork test subject loading exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class IteratorCreationException extends RuntimeException implements SubjectException
{
    /**
     * @var Suite
     */
    private $suite;
    /**
     * @var string
     */
    private $locator;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param Suite  $suite
     * @param string $locator
     */
    public function __construct($message, Suite $suite, $locator)
    {
        $this->suite = $suite;
        $this->locator = $locator;

        parent::__construct($message);
    }

    /**
     * Returns suite that cause exception.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns locator that caused exception.
     *
     * @return string
     */
    public function getLocator()
    {
        return $this->locator;
    }
}
