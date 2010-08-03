<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Step;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Loaders\StepsLoader;

class StepRunner
{
    protected $step;
    protected $definitions;
    protected $container;
    protected $tokens = array();

    protected $definition;
    protected $status;
    protected $exception;

    public function __construct(Step $step, StepsLoader $definitions, Container $container)
    {
        $this->step         = $step;
        $this->definitions  = $definitions;
        $this->container    = $container;
    }

    public function getSubject()
    {
        return $this->step;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getException()
    {
        return $this->exception;
    }

    protected function findDefinition()
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

    public function run()
    {
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
    }

    public function skip()
    {
        $this->findDefinition();

        if (null === $this->status) {
            $this->status = 'skipped';
        }
    }
}
