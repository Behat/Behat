<?php

namespace Behat\Behat\Hook;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

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
 * Hooks Container and Loader.
 * Loads & Initializates Hooks.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookDispatcher
{
    protected $resources    = array();
    protected $loaders      = array();

    protected $hooks        = array();

    /**
     * Handle Suite Events & Fire Associated Hooks. 
     * 
     * @param   Event   $event  event
     */
    public function fireSuiteHooks(Event $event)
    {
        switch ($event->getName()) {
            case 'suite.before':
                $hookName = 'suite.before';
                break;
            case 'suite.after':
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
            case 'feature.before':
                $hookName = 'feature.before';
                break;
            case 'feature.after':
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
            case 'scenario.before':
            case 'outline.example.before':
                $hookName = 'scenario.before';
                break;
            case 'scenario.after':
            case 'outline.example.after':
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
            case 'step.before':
                $hookName = 'step.before';
                break;
            case 'step.after':
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
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

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
