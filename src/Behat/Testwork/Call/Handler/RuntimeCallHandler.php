<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\Exception\CallErrorException;
use Exception;

/**
 * Handles calls in teh current runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeCallHandler implements CallHandler
{
    /**
     * @var integer
     */
    private $errorReportingLevel;

    /**
     * Initializes executor.
     *
     * @param integer $errorReportingLevel
     */
    public function __construct($errorReportingLevel = E_ALL)
    {
        $this->errorReportingLevel = $errorReportingLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCall(Call $call)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function handleCall(Call $call)
    {
        $this->startErrorAndOutputBuffering($call);
        $result = $this->executeCall($call);
        $this->stopErrorAndOutputBuffering();

        return $result;
    }

    /**
     * Used as a custom error handler when step is running.
     *
     * @see set_error_handler()
     *
     * @param integer $level
     * @param string  $message
     * @param string  $file
     * @param integer $line
     *
     * @return Boolean
     *
     * @throws CallErrorException
     */
    public function handleError($level, $message, $file, $line)
    {
        if (0 !== error_reporting()) {
            throw new CallErrorException($level, $message, $file, $line);
        }

        // error reporting turned off or more likely suppressed with @
        return false;
    }

    /**
     * Executes single call.
     *
     * @param Call $call
     *
     * @return CallResult
     */
    private function executeCall(Call $call)
    {
        $callable = $call->getBoundCallable();
        $arguments = $call->getArguments();

        $return = $exception = null;

        try {
            $return = call_user_func_array($callable, $arguments);
        } catch (Exception $caught) {
            $exception = $caught;
        }

        $stdOud = $this->getBufferedStdOut();

        return new CallResult($call, $return, $exception, $stdOud);
    }

    /**
     * Returns buffered stdout.
     *
     * @return null|string
     */
    private function getBufferedStdOut()
    {
        return ob_get_length() ? ob_get_contents() : null;
    }

    /**
     * Starts error handler and stdout buffering.
     *
     * @param Call $call
     */
    private function startErrorAndOutputBuffering(Call $call)
    {
        $errorReporting = $call->getErrorReportingLevel() ? : $this->errorReportingLevel;
        set_error_handler(array($this, 'handleError'), $errorReporting);
        ob_start();
    }

    /**
     * Stops error handler and stdout buffering.
     */
    private function stopErrorAndOutputBuffering()
    {
        ob_end_clean();
        restore_error_handler();
    }
}
