<?php

namespace Behat\Behat\RunControl\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExerciseEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Properly abort on SIGINT.
 * Subscribes to specific events and ensures proper exit of test suite on SIGINT (aka ctrl-c).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProperlyAbortOnSigint extends DispatchingService implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::BEFORE_EXERCISE => 'registerSignalListener');
    }

    /**
     * Registers listener.
     *
     * @param ExerciseEvent $event
     */
    public function registerSignalListener(ExerciseEvent $event)
    {
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, array($this, 'abortSuite'));
        }
    }

    /**
     * Aborts suite happened.
     */
    public function abortSuite()
    {
        $this->dispatch(EventInterface::AFTER_EXERCISE, new ExerciseEvent(false));

        exit(1);
    }
}
