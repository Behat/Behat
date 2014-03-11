<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Exception;

use Behat\Testwork\Call\Call;
use RuntimeException;

/**
 * Represents exceptions thrown during call handling phase.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CallHandlingException extends RuntimeException implements CallException
{
    /**
     * @var Call
     */
    private $call;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param Call   $callable
     */
    public function __construct($message, Call $callable)
    {
        $this->call = $callable;

        parent::__construct($message);
    }

    /**
     * Returns call that caused exception.
     *
     * @return Call
     */
    public function getCall()
    {
        return $this->call;
    }
}
