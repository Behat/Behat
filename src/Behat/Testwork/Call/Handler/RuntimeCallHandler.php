<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

use Behat\Testwork\Argument\Validator;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\Exception\CallErrorException;
use Exception;

/**
 * Handles calls in the current runtime.
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
     * @var bool
     */
    private $obStarted = false;
    /**
     * @var Validator
     */
    private $validator;

    /**
     * Initializes executor.
     *
     * @param integer $errorReportingLevel
     */
    public function __construct($errorReportingLevel = E_ALL)
    {
        $this->errorReportingLevel = $errorReportingLevel;
        $this->validator = new Validator();
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
     * @return bool
     *
     * @throws CallErrorException
     */
    public function handleError($level, $message, $file, $line)
    {
        if ($this->errorLevelIsNotReportable($level)) {
            return false;
        }

        throw new CallErrorException($level, $message, $file, $line);
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
        $reflection = $call->getCallee()->getReflection();
        $callable = $call->getBoundCallable();
        $arguments = $call->getArguments();
        $return = $exception = null;

        try {
            $this->validator->validateArguments($reflection, $arguments);
            $return = call_user_func_array($callable, $arguments);
        } catch (Exception $caught) {
            $exception = $caught;
        }

        $stdOut = $this->getBufferedStdOut();

        return new CallResult($call, $return, $exception, $stdOut);
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
        $this->obStarted = ob_start();
    }

    /**
     * Stops error handler and stdout buffering.
     */
    private function stopErrorAndOutputBuffering()
    {
        if ($this->obStarted) {
            ob_end_clean();
        }
        restore_error_handler();
    }

    /**
     * Checks if provided error level is not reportable.
     *
     * @param integer $level
     *
     * @return bool
     */
    private function errorLevelIsNotReportable($level)
    {
        return !(error_reporting() & $level);
    }
}
