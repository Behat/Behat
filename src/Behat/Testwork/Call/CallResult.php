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
     * Initializes call result.
     *
     * @param string|null $stdOut
     */
    public function __construct(
        private readonly Call $call,
        private $return,
        private readonly ?Exception $exception = null,
        private $stdOut = null,
    ) {
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
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * Check if call thrown exception.
     *
     * @phpstan-assert-if-true Exception $this->exception
     * @phpstan-assert-if-true Exception $this->getException()
     *
     * @return bool
     */
    public function hasException()
    {
        return $this->exception instanceof Exception;
    }

    /**
     * Returns exception thrown by call (if any).
     *
     * @return Exception|null
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
     * @return string|null
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }
}
