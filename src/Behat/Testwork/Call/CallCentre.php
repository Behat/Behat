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
use Behat\Testwork\Call\Handler\CallHandler;
use Behat\Testwork\Call\Handler\ResultHandler;

/**
 * Testwork call centre.
 *
 * Makes calls and handles results using registered handlers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CallCentre
{
    /**
     * @var CallHandler[]
     */
    private $callHandlers = array();
    /**
     * @var ResultHandler[]
     */
    private $resultHandlers = array();

    /**
     * Registers call handler.
     *
     * @param CallHandler $callHandler
     */
    public function registerCallHandler(CallHandler $callHandler)
    {
        $this->callHandlers[] = $callHandler;
    }

    /**
     * Registers call result handler.
     *
     * @param ResultHandler $resultHandler
     */
    public function registerResultHandler(ResultHandler $resultHandler)
    {
        $this->resultHandlers[] = $resultHandler;
    }

    /**
     * Handles call and its result using registered handlers and returns result.
     *
     * @param Call $call
     *
     * @return CallResult
     *
     * @throws CallHandlingException If none of the registered handlers produced call result
     */
    public function makeCall(Call $call)
    {
        return $this->handleResult($this->handleCall($call));
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

            $return = $handler->handleCall($call);

            if ($return instanceof CallResult) {
                return $return;
            }
            if ($return instanceof Call) {
                $call = $return;
            }
        }

        throw new CallHandlingException(sprintf(
            'None of the registered call handlers could handle a `%s` call.',
            $call->getCallee()->getPath()
        ), $call);
    }

    /**
     * Handles call result using registered result handlers.
     *
     * @param CallResult $result
     *
     * @return CallResult
     */
    private function handleResult(CallResult $result)
    {
        foreach ($this->resultHandlers as $handler) {
            if (!$handler->supportsResult($result)) {
                continue;
            }

            $return = $handler->handleResult($result);

            if ($return instanceof CallResult) {
                $result = $return;
            }
        }

        return $result;
    }
}
