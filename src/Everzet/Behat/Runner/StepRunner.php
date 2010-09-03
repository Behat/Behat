<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Element\StepElement;

use Everzet\Behat\Definition\StepDefinition;
use Everzet\Behat\Loader\StepsLoader;

class StepRunner extends BaseRunner implements RunnerInterface
{
    protected $step;
    protected $definitions;
    protected $dispatcher;

    protected $tokens = array();
    protected $definition;
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
        $this->tokens = $tokens;
    }

    public function getTokens()
    {
        return $this->tokens;
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

    public function findDefinition()
    {
        try {
            try {
                $this->definition = $this->definitions->findDefinition(
                    $this->step->getText($this->tokens), $this->step->getArguments()
                );
            } catch (Ambiguous $e) {
                $this->exception = $e;
                $this->status = 'failed';
            }
        } catch (Undefined $e) {
            $this->status = 'undefined';
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
                $this->status = 'failed';
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
