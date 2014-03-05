<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Output\Result\Interpretation;

use Behat\Testwork\Hook\EventDispatcher\Event\HookDispatched;
use Behat\Testwork\Tester\Result\Interpretation\ResultInterpretation;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Testwork hooked result interpretation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookedResultInterpretation implements ResultInterpretation, EventSubscriberInterface
{
    /**
     * @var Boolean
     */
    private $hasFailedHooks = false;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(HookDispatched::AFTER => 'checkIfHookThrownException');
    }

    /**
     * Checks if hook has thrown an exception and if so, mark exercise as failed.
     *
     * @param HookDispatched $event
     */
    public function checkIfHookThrownException(HookDispatched $event)
    {
        if ($event->getCallResults()->hasExceptions()) {
            $this->hasFailedHooks = true;
        }
    }

    /**
     * Checks if provided test result should be considered as a failure.
     *
     * @param TestResult $result
     *
     * @return Boolean
     */
    public function isFailure(TestResult $result)
    {
        return $this->hasFailedHooks;
    }
}
