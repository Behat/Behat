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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Error extends BehaviorException
{
    /**
     * Initializes error handler exception.
     *
     * @param   string  $code       error code
     * @param   string  $message    error message
     * @param   string  $file       error file
     * @param   string  $line       error line
     */
    public function __construct($code, $message, $file, $line)
    {
        switch ($code) {
            case E_WARNING:
            case E_USER_WARNING:
                $type = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = 'Notice';
                break;
            case E_STRICT:
                $type = 'Strict';
                break;
            case E_DEPRECATED:
                $type = 'Deprecated';
                break;
            case E_ERROR:
            case E_USER_ERROR:
            default:
                $type = 'Error';
        }

        $this->code = $code;
        $this->file = $file;
        $this->line = $line;

        parent::__construct($type . ': ' . $message);

    }
}
