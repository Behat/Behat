<?php

namespace Everzet\Behat\Hooks;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Hooks\Loader\LoaderInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
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
     * Register Event Listeners. 
     * 
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function register(EventDispatcher $dispatcher)
    {
        if (!count($this->hooks)) {
            $this->loadHooks();
        }

        foreach ($this->hooks as $hook) {
            $dispatcher->connect($hook[0], $hook[1], 2);
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

