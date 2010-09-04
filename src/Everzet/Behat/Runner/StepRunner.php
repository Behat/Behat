<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Element\StepElement;

use Everzet\Behat\Exception\Ambiguous;
use Everzet\Behat\Exception\Undefined;
use Everzet\Behat\Exception\Pending;
use Everzet\Behat\Definition\StepDefinition;
use Everzet\Behat\Loader\StepsLoader;

class StepRunner extends BaseRunner implements RunnerInterface
{
    protected $step;
    protected $definitions;
    protected $dispatcher;

    protected $definition;
    protected $snippet;
    protected $status;
    protected $exception;

    public function __construct(StepElement $step, StepsLoader $definitions, Container $container)
    {
        $this->step         = $step;
        $this->definitions  = $definitions;
        $this->dispatcher   = $container->getEventDispatcherService();
    }

    public function setTokens(array $tokens)
    {
        $this->step->setTokens($tokens);
    }

    public function getStep()
    {
        return $this->step;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function getSnippet()
    {
        return $this->snippet;
    }

    protected function findDefinition()
    {
        try {
            try {
                $this->definition = $this->definitions->findDefinition($this->step);
            } catch (Ambiguous $e) {    
                $this->status    = 'failed';
                $this->exception = $e;
            }
        } catch (Undefined $e) {
            $this->status  = 'undefined';
            $this->snippet = $this->definitions->proposeDefinition($this->step);
        }
    }

    public function run(RunnerInterface $caller = null)
    {
        $this->setCaller($caller);
        $this->dispatcher->notify(new Event($this, 'step.pre_test'));

        $this->findDefinition();

        if (null === $this->status) {
            try {
                try {
                    $this->definition->run();
                    $this->status = 'passed';
                } catch (Pending $e) {
                    $this->status = 'pending';
                }
            } catch (\Exception $e) {
                $this->status    = 'failed';
                $this->exception = $e;
            }
        }

        $this->dispatcher->notify(new Event($this, 'step.post_test'));

        return $this->status;
    }

    public function skip(RunnerInterface $caller = null)
    {
        $this->setCaller($caller);
        $this->dispatcher->notify(new Event($this, 'step.pre_test'));

        $this->findDefinition();

        if (null === $this->status) {
            $this->status = 'skipped';
        }

        $this->dispatcher->notify(new Event($this, 'step.post_test'));
    }
}
