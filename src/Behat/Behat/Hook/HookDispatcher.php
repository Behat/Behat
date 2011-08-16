<?php

namespace Behat\Behat\Hook;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\EventInterface,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\StepEvent,
    Behat\Behat\Hook\Annotation\FilterableHook;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Hook dispatcher.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookDispatcher implements EventSubscriberInterface
{
    /**
     * Loaded hooks.
     *
     * @var     array
     */
    private $hooks = array();

    /**
     * @see     Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        $events = array(
            'beforeSuite', 'afterSuite', 'beforeFeature', 'afterFeature', 'beforeScenario',
            'afterScenario', 'beforeOutlineExample', 'afterOutlineExample', 'beforeStep', 'afterStep'
        );

        return array_combine($events, $events);
    }

    /**
     * Adds hook into dispatcher.
     *
     * @param   Behat\Behat\Hook\HookInterface  $hook   hook instance
     */
    public function addHook(HookInterface $hook)
    {
        if (!isset($this->hooks[$hook->getEventName()])) {
            $this->hooks[$hook->getEventName()] = array();
        }

        $this->hooks[$hook->getEventName()][] = $hook;
    }

    /**
     * Returns all available hooks.
     *
     * @return  array
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * Removes all registered hooks.
     */
    public function removeHooks()
    {
        $this->hooks = array();
    }

    /**
     * Listens to "suite.before" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event  event to which hooks glued
     *
     * @uses    fireSuiteHooks()
     */
    public function beforeSuite(SuiteEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event  event to which hooks glued
     *
     * @uses    fireSuiteHooks()
     */
    public function afterSuite(SuiteEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Behat\Behat\Event\FeatureEvent  $event  event to which hooks glued
     *
     * @uses    fireFeatureHooks()
     */
    public function beforeFeature(FeatureEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Behat\Behat\Event\FeatureEvent  $event  event to which hooks glued
     *
     * @uses    fireFeatureHooks()
     */
    public function afterFeature(FeatureEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function beforeScenario(ScenarioEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function afterScenario(ScenarioEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Listens to "outline.example.before" event.
     *
     * @param   Behat\Behat\Event\OutlineExampleEvent   $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function beforeOutlineExample(OutlineExampleEvent $event)
    {
        $this->fireHooks('beforeScenario', $event);
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Behat\Behat\Event\OutlineExampleEvent   $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function afterOutlineExample(OutlineExampleEvent $event)
    {
        $this->fireHooks('afterScenario', $event);
    }

    /**
     * Listens to "step.before" event.
     *
     * @param   Behat\Behat\Event\StepEvent $event  event to which hooks glued
     *
     * @uses    fireStepHooks()
     */
    public function beforeStep(StepEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Behat\Behat\Event\StepEvent $event  event to which hooks glued
     *
     * @uses    fireStepHooks()
     */
    public function afterStep(StepEvent $event)
    {
        $this->fireHooks(__FUNCTION__, $event);
    }

    /**
     * Runs hooks with specified name.
     *
     * @param   string                          $name   hooks name
     * @param   Behat\Behat\Event\SuiteEvent    $event  event to which hooks glued
     */
    protected function fireHooks($name, EventInterface $event)
    {
        $hooks = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            $runable = $hook instanceof FilterableHook ? $hook->filterMatches($event) : true;

            if ($runable) {
                $hook->run($event);
            }
        }
    }
}
