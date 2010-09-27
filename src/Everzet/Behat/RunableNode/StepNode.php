<?php

namespace Everzet\Behat\RunableNode;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\StepNode as BaseNode;

use Everzet\Behat\Environment\EnvironmentInterface;
use Everzet\Behat\Loader\StepsLoader;
use Everzet\Behat\Exception\Ambiguous;
use Everzet\Behat\Exception\Undefined;
use Everzet\Behat\Exception\Pending;

class StepNode extends BaseNode implements RunableNodeInterface
{
    protected $result = 0;

    protected $definition;
    protected $exception;
    protected $snippet;

    public function getResult()
    {
        return $this->result;
    }

    public function isInBackground()
    {
        return $this->getParent() instanceof BackgroundNode;
    }

    public function isPrintable()
    {
        if ($this->getParent() instanceof BackgroundNode) {
            return $this->getParent()->isPrintable();
        } elseif ($this->getParent() instanceof ScenarioNode) {
            return !$this->getParent()->isInOutline();
        } else {
            return true;
        }
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getSnippet()
    {
        return $this->snippet;
    }

    protected function findDefinition(StepsLoader $definitions)
    {
        try {
            try {
                $this->definition = $definitions->findDefinition($this);
            } catch (Ambiguous $exc) {
                $this->result    = RunableNodeInterface::FAILED;
                $this->exception = $exc;
            }
        } catch (Undefined $exc) {
            $this->result   = RunableNodeInterface::UNDEFINED;
            $this->snippet  = $definitions->proposeDefinition($this);
        }

        return $this->definition;
    }

    public function run(Container $container, EnvironmentInterface $environment)
    {
        $dispatcher     = $container->getEventDispatcherService();
        $definitions    = $container->getStepsLoaderService();

        $this->exception    = null;
        $this->snippet      = null;
        $this->definition   = null;
        $this->result       = 0;

        $dispatcher->notify(new Event($this, 'step.run.before'));

        $definition = $this->findDefinition($definitions);

        if (0 === $this->result) {
            try {
                try {
                    $definition->run($environment);
                    $this->result = RunableNodeInterface::PASSED;
                } catch (Pending $exc) {
                    $this->result    = RunableNodeInterface::PENDING;
                    $this->exception = $exc;
                }
            } catch (\Exception $exc) {
                $this->result    = RunableNodeInterface::FAILED;
                $this->exception = $exc;
            }
        }

        $dispatcher->notify(new Event($this, 'step.run.after'));

        return $this->result;
    }

    public function skip(Container $container, EnvironmentInterface $environment)
    {
        $dispatcher     = $container->getEventDispatcherService();
        $definitions    = $container->getStepsLoaderService();

        $this->exception    = null;
        $this->snippet      = null;
        $this->definition   = null;
        $this->result       = 0;

        $dispatcher->notify(new Event($this, 'step.skip.before'));

        $this->findDefinition($definitions);

        if (0 === $this->result) {
            $this->result = RunableNodeInterface::SKIPPED;
        }

        $dispatcher->notify(new Event($this, 'step.skip.after'));

        return $this->result;
    }
}
