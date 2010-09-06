<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\DependencyInjection\Container;
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
     * @param   string      $path       base path
     * @param   Container   $container  dependency container
     */
    public function __construct($path, Container $container)
    {
        if (is_file($container->getParameter('features.file'))) {
            $file = $container->getParameter('features.file');

            $this->featuresRunner = new FeaturesRunner($file, $container);
        } else {
            $finder = new Finder();
            $files  = $finder->files()->name('*.feature')->in($path);

            $this->featuresRunner = new FeaturesRunner($files, $container);
        }
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
