<?php

namespace Behat\Behat\Callee\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Event\ExecuteCalleeEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Exception\ErrorException;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Callee executor.
 * Listens to EXECUTE_* events and executes associates callees.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CalleeExecutor implements EventSubscriberInterface
{
    private $errorReporting;

    /**
     * Initializes executor.
     *
     * @param integer $errorReporting
     */
    public function __construct($errorReporting = E_ALL)
    {
        $this->errorReporting = $errorReporting;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::EXECUTE_HOOK           => array('executeCallee', 0),
            EventInterface::EXECUTE_DEFINITION     => array('executeCallee', 0),
            EventInterface::EXECUTE_TRANSFORMATION => array('executeCallee', 0),
            EventInterface::EXECUTE_CALLEE         => array('executeCallee', 0),
        );
    }

    /**
     * Executes context-method callback.
     *
     * @param ExecuteCalleeEvent $event
     *
     * @throws Exception If callee throws exception
     */
    public function executeCallee(ExecuteCalleeEvent $event)
    {
        if ($event->isExecuted()) {
            return;
        }

        $callee = $event->getCallee();
        $callable = $callee->getCallable();

        if ($callee->isMethod()) {
            $contexts = $event->getContextPool();
            $callable = $callee->getCallable();
            $callable = array($contexts->getContext($callable[0]), $callable[1]);
        }

        set_error_handler(array($this, 'errorHandler'), $this->errorReporting);

        try {
            $return = call_user_func_array($callable, $event->getArguments());
        } catch (Exception $e) {
            restore_error_handler();

            $event->setException($e);
            $event->stopPropagation();

            throw $e;
        }

        restore_error_handler();

        $event->setReturn($return);
    }

    /**
     * Custom error handler.
     *
     * This method used as custom error handler when step is running.
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
     * @throws ErrorException
     */
    public function errorHandler($level, $message, $file, $line)
    {
        if (0 !== error_reporting()) {
            throw new ErrorException($level, $message, $file, $line);
        }

        // error reporting turned off or more likely suppressed with @
        return false;
    }
}
