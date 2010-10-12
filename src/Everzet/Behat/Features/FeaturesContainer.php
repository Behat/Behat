<?php

namespace Everzet\Behat\Features;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Features\Loader\FeatureLoaderInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Features Container and Loader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesContainer
{
    protected $container;
    protected $dispatcher;

    protected $resources    = array();
    protected $loaders      = array();
    protected $features     = array();

    /**
     * Initialize loader.
     *
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Add a loader.
     *
     * @param   string                  $format     the name of the loader
     * @param   FeatureLoaderInterface  $loader     a FeatureLoaderInterface instance
     */
    public function addLoader($format, FeatureLoaderInterface $loader)
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
     * Return features array. 
     * 
     * @return  array                       parsed features list
     */
    public function getFeatures()
    {
        if (!count($this->features)) {
            $this->loadFeatures();
        }

        return $this->features;
    }

    /**
     * Parse features with added loaders. 
     */
    protected function loadFeatures()
    {
        if (count($this->features)) {
            return;
        }

        $this->dispatcher->notify(new Event($this, 'features.load.before'));

        foreach ($this->resources as $resource) {
            if (!isset($this->loaders[$resource[0]])) {
                throw new \RuntimeException(sprintf('The "%s" features loader is not registered.', $resource[0]));
            }

            $this->features[] = $this->loaders[$resource[0]]->load($resource[1]);
        }

        $this->dispatcher->notify(new Event($this, 'features.load.after'));
    }
}

