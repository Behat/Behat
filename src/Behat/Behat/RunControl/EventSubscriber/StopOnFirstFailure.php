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
use Behat\Behat\Event\StepCollectionEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Stop on first failure.
 * Subscribes to specific events and provides ability to stop exercise on first step failure.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StopOnFirstFailure extends DispatchingService implements EventSubscriberInterface
{
    /**
     * @var Boolean
     */
    private $enabled = false;

    /**
     * Initializes subscriber.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Boolean                  $enabled
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $enabled = false)
    {
        parent::__construct($eventDispatcher);

        $this->enabled = (bool)$enabled;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_SCENARIO        => array('exitAfterCurrentScenarioOnFailure', -100),
            EventInterface::AFTER_OUTLINE_EXAMPLE => array('exitAfterCurrentScenarioOnFailure', -100),
        );
    }

    /**
     * Enables stop on failure.
     *
     * @param Boolean $enable
     */
    public function enable($enable = true)
    {
        $this->enabled = (bool)$enable;
    }

    /**
     * Exits if scenario is a failure and if stopper is enabled.
     *
     * @param StepCollectionEvent $event
     */
    public function exitAfterCurrentScenarioOnFailure(StepCollectionEvent $event)
    {
        if (!$this->enabled || StepEvent::FAILED !== $event->getResult()) {
            return;
        }

        $this->dispatch(EventInterface::AFTER_EXERCISE, new ExerciseEvent(false));

        exit(1);
    }
}
