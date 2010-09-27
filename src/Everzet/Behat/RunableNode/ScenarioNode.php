<?php

namespace Everzet\Behat\RunableNode;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\ScenarioNode as BaseNode;

class ScenarioNode extends BaseNode implements RunableNodeInterface
{
    protected $result   = 0;
    protected $skipped  = false;
    protected $outline;

    public function setOutline(OutlineNode $outline)
    {
        $this->outline = $outline;
    }

    public function getOutline()
    {
        return $this->outline;
    }

    public function isInOutline()
    {
        return null !== $this->outline;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function run(Container $container, $tokens = array())
    {
        $dispatcher     = $container->getEventDispatcherService();
        $environment    = $container->getEnvironmentService();

        $this->result   = 0;
        $this->skipped  = 0;

        $dispatcher->notify(new Event($this, 'scenario.run.before'));

        if ($this->feature->hasBackground()) {
            $result = $this->feature->getBackground()->run($container, $environment);

            if (0 !== $result) {
                $this->skipped = true;
            }
            $this->result = max($this->result, $result);
        }

        foreach ($this->getSteps() as $step) {
            if (count($tokens)) {
                $step->setTokens($tokens);
            }

            if (!$this->skipped) {
                $result = $step->run($container, $environment);

                if (0 !== $result) {
                    $this->skipped = true;
                }
            } else {
                $result = $step->skip($container, $environment);
            }
            $this->result = max($this->result, $result);
        }

        $dispatcher->notify(new Event($this, 'scenario.run.after'));

        return $this->result;
    }
}
