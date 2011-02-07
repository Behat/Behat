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
     * Register event listeners.
     *
     * @param   EventDispatcher $dispatcher
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
     * @param   Event   $event
     */
    public function beforeSuite(Event $event)
    {
        $this->fireSuiteHooks($event->getName(), $event);
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Event   $event
     */
    public function afterSuite(Event $event)
    {
        $this->fireSuiteHooks($event->getName(), $event);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Event   $event
     */
    public function beforeFeature(Event $event)
    {
        $this->fireFeatureHooks($event->getName(), $event);
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Event   $event
     */
    public function afterFeature(Event $event)
    {
        $this->fireFeatureHooks($event->getName(), $event);
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Event   $event
     */
    public function beforeScenario(Event $event)
    {
        $this->fireScenarioHooks($event->getName(), $event);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Event   $event
     */
    public function afterScenario(Event $event)
    {
        $this->fireScenarioHooks($event->getName(), $event);
    }

    /**
     * Listens to "outline.example.before" event.
     *
     * @param   Event   $event
     */
    public function beforeOutlineExample(Event $event)
    {
        $this->fireScenarioHooks('scenario.before', $event);
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Event   $event
     */
    public function afterOutlineExample(Event $event)
    {
        $this->fireScenarioHooks('scenario.after', $event);
    }

    /**
     * Listens to "step.before" event.
     *
     * @param   Event   $event
     */
    public function beforeStep(Event $event)
    {
        $this->fireStepHooks($event->getName(), $event);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Event   $event
     */
    public function afterStep(Event $event)
    {
        $this->fireStepHooks($event->getName(), $event);
    }

    /**
     * Fire suite hooks with specified name.
     *
     * @param   string  $name       hooks name
     * @param   Event   $event      event to which hooks glued
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
     * Fire feature hooks with specified name.
     *
     * @param   string  $name       hooks name
     * @param   Event   $event      event to which hooks glued
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
     * Fire scenario hooks with specified name.
     *
     * @param   string  $name       hooks name
     * @param   Event   $event      event to which hooks glued
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
     * Fire step hooks with specified name.
     *
     * @param   string  $name       hooks name
     * @param   Event   $event      event to which hooks glued
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
     * Add a loader.
     *
     * @param   string          $format     the name of the loader
     * @param   LoaderInterface $loader     a LoaderInterface instance
     */
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Add a resource.
     *
     * @param   string          $format     format of the loader
     * @param   mixed           $resource   the resource name
     */
    public function addResource($format, $resource)
    {
        $this->resources[] = array($format, $resource);
    }

    /**
     * Parse hook resources with loaders.
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
