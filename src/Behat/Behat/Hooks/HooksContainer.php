<?php

namespace Behat\Behat\Hooks;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Hooks\Loader\LoaderInterface;

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
 * Hooks Container and Loader.
 * Loads & Initializates Hooks.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HooksContainer
{
    protected $resources    = array();
    protected $loaders      = array();

    protected $hooks        = array();

    /**
     * Register Hooks Event Listeners.
     * 
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        $dispatcher->connect('suite.run.before',        array($this, 'fireSuiteHooks'));
        $dispatcher->connect('suite.run.after',         array($this, 'fireSuiteHooks'), 10);

        $dispatcher->connect('feature.run.before',      array($this, 'fireFeatureHooks'));
        $dispatcher->connect('feature.run.after',       array($this, 'fireFeatureHooks'));

        $dispatcher->connect('scenario.run.before',     array($this, 'fireScenarioHooks'));
        $dispatcher->connect('scenario.run.after',      array($this, 'fireScenarioHooks'));

        $dispatcher->connect('outline.sub.run.before',  array($this, 'fireScenarioHooks'));
        $dispatcher->connect('outline.sub.run.after',   array($this, 'fireScenarioHooks'));

        $dispatcher->connect('step.run.before',         array($this, 'fireStepHooks'));
        $dispatcher->connect('step.run.after',          array($this, 'fireStepHooks'));
    }

    /**
     * Handle Suite Events & Fire Associated Hooks. 
     * 
     * @param   Event   $event  event
     */
    public function fireSuiteHooks(Event $event)
    {
        switch ($event->getName()) {
            case 'suite.run.before':
                $hookName = 'suite.before';
                break;
            case 'suite.run.after':
                $hookName = 'suite.after';
                break;
        }

        $this->fireHooks($event, $hookName, function($hook) {return true;}, function($hook) {return true;});
    }

    /**
     * Handle Feature Events & Fire Associated Hooks. 
     * 
     * @param   Event   $event  event
     */
    public function fireFeatureHooks(Event $event)
    {
        switch ($event->getName()) {
            case 'feature.run.before':
                $hookName = 'feature.before';
                break;
            case 'feature.run.after':
                $hookName = 'feature.after';
                break;
        }

        $feature = $event->getSubject();

        $this->fireHooks($event, $hookName
            , function($hook) use($feature) {
                $tagsFilter = new TagFilter($hook[0]);

                return $tagsFilter->isFeatureMatch($feature);
            }
            , function($hook) use($feature) {
                $nameFilter = new NameFilter($hook[0]);

                return $nameFilter->isFeatureMatch($feature);
            }
        );
    }

    /**
     * Handle Scenario Events & Fire Associated Hooks. 
     * 
     * @param   Event   $event  event
     */
    public function fireScenarioHooks(Event $event)
    {
        switch ($event->getName()) {
            case 'scenario.run.before':
            case 'outline.sub.run.before':
                $hookName = 'scenario.before';
                break;
            case 'scenario.run.after':
            case 'outline.sub.run.after':
                $hookName = 'scenario.after';
                break;
        }

        $scenario = $event->getSubject();

        $this->fireHooks($event, $hookName
            , function($hook) use($scenario) {
                $tagsFilter = new TagFilter($hook[0]);

                return $tagsFilter->isScenarioMatch($scenario);
            }
            , function($hook) use($scenario) {
                $nameFilter = new NameFilter($hook[0]);

                return $nameFilter->isScenarioMatch($scenario);
            }
        );
    }

    /**
     * Handle Step Events & Fire Associated Hooks. 
     * 
     * @param   Event   $event  event
     */
    public function fireStepHooks(Event $event)
    {
        switch ($event->getName()) {
            case 'step.run.before':
                $hookName = 'step.before';
                break;
            case 'step.run.after':
                $hookName = 'step.after';
                break;
        }

        $step = $event->getSubject();

        $this->fireHooks($event, $hookName
            , function($hook) use($step) {
                $tagsFilter = new TagFilter($hook[0]);

                return $tagsFilter->isScenarioMatch($step->getParent());
            }
            , function($hook) use($step) {
                $nameFilter = new NameFilter($hook[0]);

                return $nameFilter->isScenarioMatch($step->getParent());
            }
        );
    }

    /**
     * Fire Hooks With Specified Name & Filter. 
     * 
     * @param   Event   $event      event to which hooks glued
     * @param   string  $name       hooks name
     * @param   Closure $tagsFilter tags filtering closure
     * @param   Closure $nameFilter name filtering closure
     */
    protected function fireHooks(Event $event, $name, \Closure $tagsFilter, \Closure $nameFilter)
    {
        $hooks = isset($this->hooks[$name]) ? $this->hooks[$name] : array();

        foreach ($hooks as $hook) {
            if (is_callable($hook)) {
                $hook($event);
            } elseif (!empty($hook[0]) && '@' === $hook[0][0]) {
                if ($tagsFilter($hook)) {
                    $hook[1]($event);
                }
            } elseif (!empty($hook[0])) {
                if ($nameFilter($hook)) {
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
     * Parse step hooks with added loaders. 
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
