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
 * Steps Container
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesLoader
{
    protected $featuresRunner;

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

    public function getFeaturesRunner()
    {
        return $this->featuresRunner;
    }
}
