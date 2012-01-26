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
    Behat\Behat\Hook\Annotation\FilterableHook,
    Behat\Behat\Exception\ErrorException;

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
    private $hooks  = array();
    /**
     * Dry run of hooks.
     *
     * @var     Boolean
     */
    private $dryRun = false;

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
     * Sets hook dispatcher to dry-run mode.
     *
     * @param   Boolean $dryRun
     */
    public function setDryRun($dryRun = true)
    {
        $this->dryRun = (bool) $dryRun;
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
     * Custom error handler.
     *
     * This method used as custom error handler when step is running.
     *
     * @see     set_error_handler()
     *
     * @throws  Behat\Behat\Exception\ErrorException
     */
    public function errorHandler($code, $message, $file, $line)
    {
        if (0 === error_reporting()) {
            return; // error reporting turned off or more likely suppresed with @
        }
        throw new ErrorException($code, $message, $file, $line);
    }

    /**
     * Runs hooks with specified name.
     *
     * @param   string                          $name   hooks name
     * @param   Behat\Behat\Event\SuiteEvent    $event  event to which hooks glued
     */
    protected function fireHooks($name, EventInterface $event)
    {
        if ($this->dryRun) {
            return;
        }

        $hooks = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            $runable = $hook instanceof FilterableHook ? $hook->filterMatches($event) : true;

            if ($runable) {
                if (defined('BEHAT_ERROR_REPORTING')) {
                    $errorLevel = BEHAT_ERROR_REPORTING;
                } else {
                    $errorLevel = E_ALL ^ E_WARNING;
                }

                $oldHandler = set_error_handler(array($this, 'errorHandler'), $errorLevel);

                try {
                    $hook->run($event);
                } catch (\Exception $e) {
                    $this->addHookInformationToException($hook, $e);
                    throw $e;
                }

                if (null !== $oldHandler) {
                    set_error_handler($oldHandler);
                }
            }
        }
    }

    /**
     * Adds hook information to exception thrown from it.
     *
     * @param   Behat\Behat\Hook\HookInterface  $hook       hook instance
     * @param   Exception                       $exception  exception
     */
    private function addHookInformationToException(HookInterface $hook, \Exception $exception)
    {
        $refl    = new \ReflectionObject($exception);
        $message = $refl->getProperty('message');

        $message->setAccessible(true);
        $message->setValue($exception, sprintf(
            'Exception has been thrown in "%s" hook, defined in %s'."\n\n%s",
            $hook->getEventName(),
            $hook->getPath(),
            $exception->getMessage()
        ));
    }
}
