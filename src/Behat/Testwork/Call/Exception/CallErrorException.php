<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Exception;

use ErrorException;

/**
 * Represents catchable errors raised during call execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CallErrorException extends ErrorException
{
    private $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
    );

    /**
     * Initializes error handler exception.
     *
     * @param integer $level   error level
     * @param string  $message error message
     * @param string  $file    error file
     * @param integer $line    error line
     */
    public function __construct($level, $message, $file, $line)
    {
        parent::__construct(
            sprintf(
                '%s: %s in %s line %d',
                $this->levels[$level] ?? $level,
                $message,
                $file,
                $line
            )
        );
    }
}
