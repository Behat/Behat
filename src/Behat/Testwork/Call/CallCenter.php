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
use Behat\Testwork\Call\Filter\CallFilter;
use Behat\Testwork\Call\Filter\ResultFilter;
use Behat\Testwork\Call\Handler\CallHandler;
use Exception;

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
    private $callFilters = array();
    /**
     * @var CallHandler[]
     */
    private $callHandlers = array();
    /**
     * @var ResultFilter[]
     */
    private $resultFilters = array();

    /**
     * Registers call filter.
     *
     * @param CallFilter $filter
     */
    public function registerCallFilter(CallFilter $filter)
    {
        $this->callFilters[] = $filter;
    }

    /**
     * Registers call handler.
     *
     * @param CallHandler $handler
     */
    public function registerCallHandler(CallHandler $handler)
    {
        $this->callHandlers[] = $handler;
    }

    /**
     * Registers call result filter.
     *
     * @param ResultFilter $filter
     */
    public function registerResultFilter(ResultFilter $filter)
    {
        $this->resultFilters[] = $filter;
    }

    /**
     * Handles call and its result using registered filters and handlers.
     *
     * @param Call $call
     *
     * @return CallResult
     */
    public function makeCall(Call $call)
    {
        try {
            $filteredCall = $this->filterCall($call);
            $result = $this->handleCall($filteredCall);
            $filteredResult = $this->filterResult($result);
        } catch (Exception $e) {
            return new CallResult($call, null, $e, null);
        }

        return $filteredResult;
    }

    /**
     * Filters call using registered filters and returns a filtered one.
     *
     * @param Call $call
     *
     * @return Call
     */
    private function filterCall(Call $call)
    {
        foreach ($this->callFilters as $filter) {
            if (!$filter->supportsCall($call)) {
                continue;
            }

            return $filter->filterCall($call);
        }

        return $call;
    }

    /**
     * Handles call using registered call handlers.
     *
     * @param Call $call
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
     * @param CallResult $result
     *
     * @return CallResult
     */
    private function filterResult(CallResult $result)
    {
        foreach ($this->resultFilters as $filter) {
            if (!$filter->supportsResult($result)) {
                continue;
            }

            return $filter->filterResult($result);
        }

        return $result;
    }
}
