<?php

namespace Behat\Behat\Suite\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Suite\Event\SuitesCarrierEvent;
use Behat\Behat\Suite\SuiteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Suites loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuitesLoader implements EventSubscriberInterface
{
    /**
     * @var SuiteInterface[]
     */
    private $suites = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::LOAD_SUITES => array('setRegisteredSuites', 0)
        );
    }

    /**
     * Registers a suite.
     *
     * @param SuiteInterface $suite
     */
    public function registerSuite(SuiteInterface $suite)
    {
        $this->suites[$suite->getName()] = $suite;
    }

    /**
     * Sets registered suites to the carrier event.
     *
     * @param SuitesCarrierEvent $event
     */
    public function setRegisteredSuites(SuitesCarrierEvent $event)
    {
        array_map(array($event, 'addSuite'), $this->suites);
    }
}
