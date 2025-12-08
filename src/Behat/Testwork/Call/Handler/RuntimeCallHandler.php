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
     * @var bool
     */
    private $obStarted = false;
    /**
     * @var Validator
     */
    private $validator;

    private ?int $previousErrorReporting = null;

    /**
     * Initializes executor.
     *
     * @param int $errorReportingLevel
     */
    public function __construct(
        private $errorReportingLevel = E_ALL,
    ) {
        $this->validator = new Validator();
    }

    public function supportsCall(Call $call): bool
    {
        return true;
    }

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
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws CallErrorException
     */
    public function handleError($level, $message, $file, $line): bool
    {
        if ($this->errorLevelIsNotReportable($level)) {
            return false;
        }

        throw new CallErrorException($level, $message, $file, $line);
    }

    /**
     * Executes single call.
     */
    private function executeCall(Call $call): CallResult
    {
        $reflection = $call->getCallee()->getReflection();
        $callable = $call->getBoundCallable();
        $arguments = $call->getArguments();
        $return = $exception = null;

        try {
            $arguments = array_values($arguments);
            $this->validator->validateArguments($reflection, $arguments);
            $return = $callable(...$arguments);
        } catch (Exception $caught) {
            $exception = $caught;
        }

        $stdOut = $this->getBufferedStdOut();

        return new CallResult($call, $return, $exception, $stdOut);
    }

    /**
     * Returns buffered stdout.
     *
     * @return string|null
     */
    private function getBufferedStdOut()
    {
        return ob_get_length() ? ob_get_contents() : null;
    }

    /**
     * Starts error handler and stdout buffering.
     */
    private function startErrorAndOutputBuffering(Call $call): void
    {
        $errorReporting = $call->getErrorReportingLevel() ?: $this->errorReportingLevel;
        $this->previousErrorReporting = error_reporting($errorReporting);
        set_error_handler($this->handleError(...), $errorReporting);
        $this->obStarted = ob_start();
    }

    /**
     * Stops error handler and stdout buffering.
     */
    private function stopErrorAndOutputBuffering(): void
    {
        if ($this->obStarted) {
            ob_end_clean();
        }
        restore_error_handler();
        if (null !== $this->previousErrorReporting) {
            error_reporting($this->previousErrorReporting);
            $this->previousErrorReporting = null;
        }
    }

    /**
     * Checks if provided error level is not reportable.
     *
     * @param int $level
     */
    private function errorLevelIsNotReportable($level): bool
    {
        return !(error_reporting() & $level);
    }
}
