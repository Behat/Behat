<?php

namespace Behat\Behat\Hook;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Hook\Loader\LoaderInterface;

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
class HookDispatcher
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
     * Registers event listeners.
     *
     * @param   Behat\Behat\EventDispatcher\EventDispatcher $dispatcher
     *
     * @uses    beforeSuite()
     * @uses    afterSuite()
     * @uses    beforeFeature()
     * @uses    afterFeature()
     * @uses    beforeScenario()
     * @uses    afterScenario()
     * @uses    beforeOutlineExample()
     * @uses    afterOutlineExample()
     * @uses    beforeStep()
     * @uses    afterStep()
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('suite.before',            array($this, 'beforeSuite'),            10);
        $dispatcher->connect('suite.after',             array($this, 'afterSuite'),             10);
        $dispatcher->connect('feature.before',          array($this, 'beforeFeature'),          10);
        $dispatcher->connect('feature.after',           array($this, 'afterFeature'),           10);
        $dispatcher->connect('scenario.before',         array($this, 'beforeScenario'),         10);
        $dispatcher->connect('scenario.after',          array($this, 'afterScenario'),          10);
        $dispatcher->connect('outline.example.before',  array($this, 'beforeOutlineExample'),   10);
        $dispatcher->connect('outline.example.after',   array($this, 'afterOutlineExample'),    10);
        $dispatcher->connect('step.before',             array($this, 'beforeStep'),             10);
        $dispatcher->connect('step.after',              array($this, 'afterStep'),              10);
    }

    /**
     * Listens to "suite.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireSuiteHooks()
     */
    public function beforeSuite(Event $event)
    {
        $this->fireSuiteHooks($event->getName(), $event);
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireSuiteHooks()
     */
    public function afterSuite(Event $event)
    {
        $this->fireSuiteHooks($event->getName(), $event);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireFeatureHooks()
     */
    public function beforeFeature(Event $event)
    {
        $this->fireFeatureHooks($event->getName(), $event);
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireFeatureHooks()
     */
    public function afterFeature(Event $event)
    {
        $this->fireFeatureHooks($event->getName(), $event);
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function beforeScenario(Event $event)
    {
        $this->fireScenarioHooks($event->getName(), $event);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function afterScenario(Event $event)
    {
        $this->fireScenarioHooks($event->getName(), $event);
    }

    /**
     * Listens to "outline.example.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function beforeOutlineExample(Event $event)
    {
        $this->fireScenarioHooks('scenario.before', $event);
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireScenarioHooks()
     */
    public function afterOutlineExample(Event $event)
    {
        $this->fireScenarioHooks('scenario.after', $event);
    }

    /**
     * Listens to "step.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireStepHooks()
     */
    public function beforeStep(Event $event)
    {
        $this->fireStepHooks($event->getName(), $event);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     *
     * @uses    fireStepHooks()
     */
    public function afterStep(Event $event)
    {
        $this->fireStepHooks($event->getName(), $event);
    }

    /**
     * Runs suite hooks with specified name.
     *
     * @param   string                                      $name   hooks name
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     */
    protected function fireSuiteHooks($name, Event $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $hooks = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                $hook($event);
            } else {
                $hook[1]($event);
            }
        }
    }

    /**
     * Runs feature hooks with specified name.
     *
     * @param   string                                      $name   hooks name
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     */
    protected function fireFeatureHooks($name, Event $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $feature    = $event->getSubject();
        $hooks      = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                $hook($event);
            } elseif (!empty($hook[0]) && '@' === $hook[0][0]) {
                $filter = new TagFilter($hook[0]);

                if ($filter->isFeatureMatch($feature)) {
                    $hook[1]($event);
                }
            } elseif (!empty($hook[0])) {
                $filter = new NameFilter($hook[0]);

                if ($filter->isFeatureMatch($feature)) {
                    $hook[1]($event);
                }
            } else {
                $hook[1]($event);
            }
        }
    }

    /**
     * Runs scenario hooks with specified name.
     *
     * @param   string                                      $name   hooks name
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     */
    protected function fireScenarioHooks($name, Event $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $scenario   = $event->getSubject();
        $hooks      = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                $hook($event);
            } elseif (!empty($hook[0]) && '@' === $hook[0][0]) {
                $filter = new TagFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    $hook[1]($event);
                }
            } elseif (!empty($hook[0])) {
                $filter = new NameFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    $hook[1]($event);
                }
            } else {
                $hook[1]($event);
            }
        }
    }

    /**
     * Runs step hooks with specified name.
     *
     * @param   string                                      $name   hooks name
     * @param   Symfony\Component\EventDispatcher\Event     $event  event to which hooks glued
     */
    protected function fireStepHooks($name, Event $event)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $scenario   = $event->getSubject()->getParent();
        $hooks      = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                $hook($event);
            } elseif (!empty($hook[0]) && '@' === $hook[0][0]) {
                $filter = new TagFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    $hook[1]($event);
                }
            } elseif (!empty($hook[0])) {
                $filter = new NameFilter($hook[0]);

                if ($filter->isScenarioMatch($scenario)) {
                    $hook[1]($event);
                }
            } else {
                $hook[1]($event);
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

            $this->hooks = array_merge($this->hooks, $this->loaders[$resource[0]]->load($resource[1]));
        }
    }
}
