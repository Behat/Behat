<?php

namespace Everzet\Behat\Loaders;

use \Symfony\Components\DependencyInjection\Container;
use \Symfony\Components\Finder\Finder;

use \Everzet\Behat\Runners\FeatureRunner;

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
class FeaturesLoader implements \Iterator
{
    protected $pos = 0;
    protected $featureRunners = array();

    public function __construct($path, Container $container)
    {
        $finder = new Finder();
        $parser = $container->getParserService();

        foreach ($finder->files()->name('*.feature')->in($path) as $featureFile) {
            $this->featureRunners[] = new FeatureRunner(
                $parser->parseFile($featureFile), $container
            );
        }
    }

    public function current()
    {
        return $this->featureRunners[$this->pos];
    }

    public function key()
    {
        return $this->pos;
    }

    public function next()
    {
        ++$this->pos;
    }

    public function rewind()
    {
        $this->pos = 0;
    }

    public function valid()
    {
        return isset($this->featureRunners[$this->pos]);
    }
}
