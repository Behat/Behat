<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use Behat\Testwork\Call\Exception\CallHandlingException;
use Behat\Testwork\Call\Exception\FatalThrowableError;
use Behat\Testwork\Call\Filter\CallFilter;
use Behat\Testwork\Call\Filter\ResultFilter;
use Behat\Testwork\Call\Handler\CallHandler;
use Behat\Testwork\Call\Handler\ExceptionHandler;
use Exception;
use Throwable;

/**
 * Makes calls and handles results using registered handlers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CallCenter
{
    /**
     * @var CallFilter[]
     */
    private $callFilters = [];
    /**
     * @var CallHandler[]
     */
    private $callHandlers = [];
    /**
     * @var ResultFilter[]
     */
    private $resultFilters = [];
    /**
     * @var ExceptionHandler[]
     */
    private $exceptionHandlers = [];

    /**
     * Registers call filter.
     */
    public function registerCallFilter(CallFilter $filter)
    {
        $this->callFilters[] = $filter;
    }

    /**
     * Registers call handler.
     */
    public function registerCallHandler(CallHandler $handler)
    {
        $this->callHandlers[] = $handler;
    }

    /**
     * Registers call result filter.
     */
    public function registerResultFilter(ResultFilter $filter)
    {
        $this->resultFilters[] = $filter;
    }

    /**
     * Registers result exception handler.
     */
    public function registerExceptionHandler(ExceptionHandler $handler)
    {
        $this->exceptionHandlers[] = $handler;
    }

    /**
     * Handles call and its result using registered filters and handlers.
     *
     * @return CallResult
     */
    public function makeCall(Call $call)
    {
        try {
            return $this->filterResult($this->handleCall($this->filterCall($call)));
        } catch (Throwable $exception) {
            return new CallResult($call, null, $this->handleException($exception), null);
        }
    }

    /**
     * Filters call using registered filters and returns a filtered one.
     *
     * @return Call
     */
    private function filterCall(Call $call)
    {
        foreach ($this->callFilters as $filter) {
            if (!$filter->supportsCall($call)) {
                continue;
            }

            $call = $filter->filterCall($call);
        }

        return $call;
    }

    /**
     * Handles call using registered call handlers.
     *
     * @return CallResult
     *
     * @throws CallHandlingException If call handlers didn't produce call result
     */
    private function handleCall(Call $call)
    {
        foreach ($this->callHandlers as $handler) {
            if (!$handler->supportsCall($call)) {
                continue;
            }

            return $handler->handleCall($call);
        }

        throw new CallHandlingException(sprintf(
            'None of the registered call handlers could handle a `%s` call.',
            $call->getCallee()->getPath()
        ), $call);
    }

    /**
     * Filters call result using registered filters and returns a filtered one.
     *
     * @return CallResult
     */
    private function filterResult(CallResult $result)
    {
        foreach ($this->resultFilters as $filter) {
            if (!$filter->supportsResult($result)) {
                continue;
            }

            $result = $filter->filterResult($result);
        }

        return $result;
    }

    /**
     * Handles exception using registered handlers and returns a handled one.
     *
     * @param Throwable $exception
     *
     * @return Exception
     */
    private function handleException($exception)
    {
        foreach ($this->exceptionHandlers as $handler) {
            if (!$handler->supportsException($exception)) {
                continue;
            }

            $exception = $handler->handleException($exception);
        }

        if (!$exception instanceof Exception) {
            return new FatalThrowableError($exception);
        }

        return $exception;
    }
}
