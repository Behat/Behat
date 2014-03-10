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
     * @var string
     */
    private $name;

    /**
     * Initializes exception.
     *
     * @param string         $message
     * @param string         $name
     * @param Exception|null $previous
     */
    public function __construct($message, $name, Exception $previous = null)
    {
        $this->name = $name;

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
