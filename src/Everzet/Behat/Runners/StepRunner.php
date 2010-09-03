<?php

namespace Everzet\Behat\Runners;

use Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Step;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Loaders\StepsLoader;
use \Everzet\Behat\Loggers\Logger;

class StepRunner extends BaseRunner implements Runner
{
    protected $step;
    protected $definitions;
    protected $container;
    protected $tokens = array();

    protected $definition;
    protected $status;
    protected $exception;

    public function __construct(Step $step, StepsLoader $definitions, Container $container,
                                Logger $logger)
    {
        $this->step         = $step;
        $this->definitions  = $definitions;
        $this->container    = $container;
        $this->setLogger(     $logger);
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

    public function run(Runner $caller = null)
    {
        $this->setCaller($caller);
        $this->getLogger()->beforeStep($this);

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

        $this->getLogger()->afterStep($this);

        return $this->status;
    }

    public function skip(Runner $caller = null)
    {
        $this->setCaller($caller);
        $this->getLogger()->beforeStep($this);

        $this->findDefinition();

        if (null === $this->status) {
            $this->status = 'skipped';
        }

        $this->getLogger()->afterStep($this);
    }
}
