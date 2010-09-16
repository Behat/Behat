<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Finder\Finder;

use Everzet\Behat\Runner\FeaturesRunner;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loads feature/features & pass them into FeaturesRunner.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesLoader implements LoaderInterface
{
    protected $container;
    protected $dispatcher;
    protected $featuresRunner;

    /**
     * Inits loader.
     *
     * @param   Container       $container  dependency container
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function __construct(Container $container, EventDispatcher $dispatcher)
    {
        $this->container    = $container;
        $this->dispatcher   = $dispatcher;
    }

    /**
     * Loads features from specified path(s)
     *
     * @param   string|array    $paths  features path(s)
     * 
     * @return  FeaturesRunner          features runner instance
     */
    public function load($paths)
    {
        $this->dispatcher->notify(new Event($this, 'features.load.before'));

        if (!is_array($paths) && is_file($paths)) {
            $this->featuresRunner = new FeaturesRunner($paths, $this->container);
        } else {
            foreach ((array) $paths as $path) {
                $finder = new Finder();
                $files  = $finder->files()->name('*.feature')->in($path);
                $this->featuresRunner = new FeaturesRunner($files, $this->container);
            }
        }

        $this->dispatcher->notify(new Event($this, 'features.load.after'));

        return $this->featuresRunner;
    }

    /**
     * Returns created FeaturesRunner
     *
     * @return  Everzet\Behat\Runner\FeaturesRunner
     */
    public function getFeaturesRunner()
    {
        return $this->featuresRunner;
    }
}
