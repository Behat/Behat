<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Finder\Finder;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Features Loader.
 * Load feature/features.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesLoader implements LoaderInterface
{
    protected $container;
    protected $dispatcher;

    /**
     * Initialize loader.
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
     * Load features from specified path(s).
     *
     * @param   string|array    $paths  features path(s)
     * 
     * @return  FeaturesRunner          features runner instance
     */
    public function load($paths)
    {
        $features = array();

        $this->dispatcher->notify(new Event($this, 'features.load.before'));

        if (!is_array($paths) && is_file($paths)) {
            $features[] = $this->container->getParserService()->parseFile($paths);
        } else {
            foreach ((array) $paths as $path) {
                $finder = new Finder();
                $files  = $finder->files()->name('*.feature')->in($path);

                foreach ($files as $file) {
                    $features[] = $this->container->getParserService()->parseFile($file);
                }
            }
        }

        $this->dispatcher->notify(new Event($this, 'features.load.after'));

        return $features;
    }
}
