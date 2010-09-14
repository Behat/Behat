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
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesLoader
{
    protected $featuresRunner;

    /**
     * Inits loader.
     *
     * @param   string|array    $paths      features path(s)
     * @param   Container       $container  dependency container
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function __construct($paths, Container $container, EventDispatcher $dispatcher)
    {
        $dispatcher->notify(new Event($this, 'features.load.before'));

        if (is_file($container->getParameter('features.file'))) {
            $file = $container->getParameter('features.file');

            $this->featuresRunner = new FeaturesRunner($file, $container);
        } else {
            foreach ((array) $paths as $path) {
                $finder = new Finder();
                $files  = $finder->files()->name('*.feature')->in($path);
                $this->featuresRunner = new FeaturesRunner($files, $container);
            }
        }

        $dispatcher->notify(new Event($this, 'features.load.after'));
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
