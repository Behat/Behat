<?php

namespace Behat\Behat\Exception;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Error handler exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ErrorException extends BehaviorException
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
     * @param string $level   error level
     * @param string $message error message
     * @param string $file    error file
     * @param string $line    error line
     */
    public function __construct($level, $message, $file, $line)
    {
        parent::__construct(sprintf('%s: %s in %s line %d',
            isset($this->levels[$level]) ? $this->levels[$level] : $level,
            $message,
            $file,
            $line
        ));
    }
}
