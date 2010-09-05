<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

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

    protected $definition;
    protected $snippet;
    protected $status;
    protected $exception;

    public function __construct(StepElement $step, StepsLoader $definitions, Container $container,
                                RunnerInterface $parent)
    {
        $this->step         = $step;
        $this->definitions  = $definitions;

        parent::__construct('step', $container->getEventDispatcherService(), $parent);
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

    public function getDefinitionSnippet()
    {
        return $this->snippet;
    }

    public function getStepsCount()
    {
        return 1;
    }

    public function getStepsStatusesCount()
    {
        return array($this->getStatus() => 1);
    }

    public function getFailedStepRunners()
    {
        return 'failed' === $this->status ? array($this) : array();
    }

    public function getPendingStepRunners()
    {
        return 'pending' === $this->status ? array($this) : array();
    }

    public function getDefinitionSnippets()
    {
        return is_array($this->snippet) ? array(md5($this->snippet[1]) => $this->snippet) : array();
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

    protected function doRun()
    {
        $this->findDefinition();

        if (null === $this->status) {
            try {
                try {
                    $this->definition->run();
                    $this->status = 'passed';
                } catch (Pending $e) {
                    $this->status    = 'pending';
                    $this->exception = $e;
                }
            } catch (\Exception $e) {
                $this->status    = 'failed';
                $this->exception = $e;
            }
        }
    }

    public function skip()
    {
        $this->fireEvent('pre_skip');

        $this->findDefinition();

        if (null === $this->status) {
            $this->status = 'skipped';
        }

        $this->fireEvent('post_skip');

        return $this->getStatusCode();
    }
}
