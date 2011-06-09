<?php

namespace Behat\Behat\Hook;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Hook\Loader\LoaderInterface,
    Behat\Behat\Event\EventInterface,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\StepEvent;

use Behat\Gherkin\Filter\TagFilter,
    Behat\Gherkin\Filter\NameFilter;

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
     * Hook resources.
     *
     * @var     array
     */
    protected $resources    = array();
    /**
     * Hook loaders.
     *
     * @var     array
     */
    protected $loaders      = array();
    /**
     * Loaded hooks.
     *
     * @var     array
     */
    protected $hooks        = array();

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
     * Listens to "suite.before" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event  event to which hooks glued
     *
     * @uses    fireSuiteHooks()
     */
    public function beforeSuite(SuiteEvent $event)
    {
        $this->fireSuiteHooks('suite.before', $event);
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
        $this->fireSuiteHooks('suite.after', $event);
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
        $this->fireFeatureHooks('feature.before', $event);
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
        $this->fireFeatureHooks('feature.after', $event);
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
        $this->fireScenarioHooks('scenario.before', $event);
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
        $this->fireScenarioHooks('scenario.after', $event);
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
        $this->fireScenarioHooks('scenario.before', $event);
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
        $this->fireScenarioHooks('scenario.after', $event);
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
        $this->fireStepHooks('step.before', $event);
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
        $this->fireStepHooks('step.after', $event);
    }

    /**
     * Runs suite hooks with specified name.
     *
     * @param   string                          $name   hooks name
     * @param   Behat\Behat\Event\SuiteEvent    $event  event to which hooks glued
     */
    protected function fireSuiteHooks($name, SuiteEvent $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $hooks = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            call_user_func($hook, $event);
        }
    }

    /**
     * Runs feature hooks with specified name.
     *
     * @param   string                          $name   hooks name
     * @param   Behat\Behat\Event\FeatureEvent  $event  event to which hooks glued
     */
    protected function fireFeatureHooks($name, FeatureEvent $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $feature    = $event->getFeature();
        $hooks      = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                call_user_func($hook, $event);
            } elseif (!empty($hook[0]) && false !== strpos($hook[0], '@')) {
                $filter = new TagFilter($hook[0]);

                if ($filter->isFeatureMatch($feature)) {
                    call_user_func($hook[1], $event);
                }
            } elseif (!empty($hook[0])) {
                $filter = new NameFilter($hook[0]);

                if ($filter->isFeatureMatch($feature)) {
                    call_user_func($hook[1], $event);
                }
            } else {
                call_user_func($hook[1], $event);
            }
        }
    }

    /**
     * Runs scenario hooks with specified name.
     *
     * @param   string                              $name   hooks name
     * @param   Behat\Behat\Event\EventInterface    $event  event to which hooks glued
     */
    protected function fireScenarioHooks($name, EventInterface $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        if ($event instanceof ScenarioEvent) {
            $scenario = $event->getScenario();
        } else {
            $scenario = $event->getOutline();
        }
        $hooks = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                call_user_func($hook, $event);
            } elseif (!empty($hook[0]) && false !== strpos($hook[0], '@')) {
                $filter = new TagFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    call_user_func($hook[1], $event);
                }
            } elseif (!empty($hook[0])) {
                $filter = new NameFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    call_user_func($hook[1], $event);
                }
            } else {
                call_user_func($hook[1], $event);
            }
        }
    }

    /**
     * Runs step hooks with specified name.
     *
     * @param   string                      $name   hooks name
     * @param   Behat\Behat\Event\StepEvent $event  event to which hooks glued
     */
    protected function fireStepHooks($name, StepEvent $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $scenario   = $event->getStep()->getParent();
        $hooks      = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                call_user_func($hook, $event);
            } elseif (!empty($hook[0]) && false !== strpos($hook[0], '@')) {
                $filter = new TagFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    call_user_func($hook[1], $event);
                }
            } elseif (!empty($hook[0])) {
                $filter = new NameFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    call_user_func($hook[1], $event);
                }
            } else {
                call_user_func($hook[1], $event);
            }
        }
    }

    /**
     * Adds a hook resource loader.
     *
     * @param   string                                      $name   loader format name
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     */
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Adds a hook resource to load.
     *
     * @param   string          $format     loader format name
     * @param   mixed           $resource   the resource name
     */
    public function addResource($format, $resource)
    {
        $this->resources[] = array($format, $resource);
    }

    /**
     * Loads all hook resources with loaders.
     *
     * @throws  RuntimeException    if loader for specified format is not registered
     */
    protected function loadHooks()
    {
        if (count($this->hooks)) {
            return;
        }

        foreach ($this->resources as $resource) {
            if (!isset($this->loaders[$resource[0]])) {
                throw new \RuntimeException(sprintf('The "%s" step hook loader is not registered.', $resource[0]));
            }

            $this->hooks = array_merge_recursive(
                $this->hooks, $this->loaders[$resource[0]]->load($resource[1])
            );
        }
    }
}
