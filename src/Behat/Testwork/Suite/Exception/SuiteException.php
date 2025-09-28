<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Exception;

use Behat\Testwork\Exception\TestworkException;
use Exception;
use InvalidArgumentException;

/**
 * Represents a suite exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteException extends InvalidArgumentException implements TestworkException
{
    /**
     * Initializes exception.
     *
     * @param string         $message
     * @param string         $name
     */
    public function __construct(
        $message,
        private $name,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Returns name of the suite that caused exception.
     *
     * @return string
     */
    public function getSuiteName()
    {
        return $this->name;
    }
}
