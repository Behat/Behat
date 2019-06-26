<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use Exception;

/**
 * Represents result of the call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CallResult
{
    /**
     * @var Call
     */
    private $call;
    /**
     * @var mixed
     */
    private $return;
    /**
     * @var null|Exception
     */
    private $exception;
    /**
     * @var null|string
     */
    private $stdOut;

    /**
     * Initializes call result.
     *
     * @param Call           $call
     * @param mixed          $return
     * @param null|Exception $exception
     * @param null|string    $stdOut
     */
    public function __construct(Call $call, $return, Exception $exception = null, $stdOut = null)
    {
        $this->call = $call;
        $this->return = $return;
        $this->exception = $exception;
        $this->stdOut = $stdOut;
    }

    /**
     * Returns call.
     *
     * @return Call
     */
    public function getCall()
    {
        return $this->call;
    }

    /**
     * Returns call return value.
     *
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * Check if call thrown exception.
     *
     * @return bool
     */
    public function hasException()
    {
        return null !== $this->exception;
    }

    /**
     * Returns exception thrown by call (if any).
     *
     * @return null|Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Checks if call produced stdOut.
     *
     * @return bool
     */
    public function hasStdOut()
    {
        return null !== $this->stdOut;
    }

    /**
     * Returns stdOut produced by call (if any).
     *
     * @return null|string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }
}
