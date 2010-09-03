<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

class FeaturesRunner implements RunnerInterface, \Iterator
{
    protected $position         = 0;
    protected $featureRunners   = array();

    public function __construct(Finder $featureFiles, Container $container)
    {
        $this->position = 0;

        foreach ($featureFiles as $file) {
            $this->featureRunners[] = new FeatureRunner(
                $container->getParserService()->parseFile($file)
              , $container
              , $container->getLoggerService()
            );
        }
    }

    public function key()
    {
        return $this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->featureRunners[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->featureRunners[$this->position]);
    }

    public function run(RunnerInterface $parent = null)
    {
        foreach ($this as $runner) {
            $runner->run($this);
        }
    }
}
