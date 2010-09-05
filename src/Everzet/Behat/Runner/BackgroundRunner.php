<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\Scenario\BackgroundElement;

use Everzet\Behat\Loader\StepsLoader;

class BackgroundRunner extends BaseRunner implements RunnerInterface
{
    protected $background;
    protected $skip = false;

    public function __construct(BackgroundElement $background, StepsLoader $definitions, 
                                Container $container, RunnerInterface $parent = null)
    {
        $this->background = $background;

        foreach ($background->getSteps() as $step) {
            $this->addChildRunner(new StepRunner($step, $definitions, $container, $this));
        }

        parent::__construct('background', $container->getEventDispatcherService(), $parent);
    }

    public function isSkipped()
    {
        return $this->skip;
    }

    public function getBackground()
    {
        return $this->background;
    }

    protected function doRun()
    {
        foreach ($this as $runner) {
            if (!$this->skip) {
                if (0 !== $runner->run()) {
                    $this->skip = true;
                }
            } else {
                $runner->skip();
            }
        }
    }
}
