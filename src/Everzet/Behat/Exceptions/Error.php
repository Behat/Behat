<?php

namespace Everzet\Behat\Exceptions;

use \Everzet\Behat\Exceptions\BehaviorException as BaseException;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Error handler Exception
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Error extends BaseException
{
    /**
     * Creates Error handler exception
     *
     * @param   string  $code       error code
     * @param   string  $message    error message
     * @param   string  $file       error file
     * @param   string  $line       error line
     */
    public function __construct($code, $message, $file, $line)
    {
        parent::__construct();

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
        $this->message = $type . ': ' . $message;
        $this->file = $file;
        $this->line = $line;
    }
}
