<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Behat\Runner\RunnerInterface;
use Everzet\Behat\Loader\StepsLoader;

abstract class StepsRunner extends BaseRunner implements \Iterator
{
    protected $position     = 0;
    protected $stepRunners  = array();
    protected $skip         = false;

    protected function __construct(array $steps, StepsLoader $definitions, 
                                       Container $container)
    {
        $this->position = 0;

        foreach ($steps as $step) {
            $this->stepRunners[] = new StepRunner(
                $step
              , $definitions
              , $container
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
        return $this->stepRunners[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->stepRunners[$this->position]);
    }

    public function getStepRunners()
    {
        return $this->stepRunners;
    }

    public function getStatus()
    {
        return $this->getStatusFromArray($this->getStepRunners());
    }

    protected function runStepTest(RunnerInterface $runner)
    {
        if (!$this->skip) {
            if ('passed' !== $runner->run($this)) {
                $this->skip = true;
            }
        } else {
            $runner->skip($this);
        }
    }
}
