<?php

namespace Everzet\Behat\RunableNode;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\BackgroundNode as BaseNode;

use Everzet\Behat\Environment\EnvironmentInterface;

class BackgroundNode extends BaseNode implements RunableNodeInterface
{
    protected $result       = 0;
    protected $skipped      = false;
    protected $printable    = false;

    public function getResult()
    {
        return $this->result;
    }

    public function setPrintable($isPrintable = true)
    {
        $this->printable = $isPrintable;
    }

    public function isPrintable()
    {
        return $this->printable;
    }

    public function run(Container $container, EnvironmentInterface $environment)
    {
        $dispatcher = $container->getEventDispatcherService();

        $dispatcher->notify(new Event($this, 'background.run.before'));

        $this->result   = 0;
        $this->skipped  = false;

        foreach ($this->getSteps() as $step) {
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

        $dispatcher->notify(new Event($this, 'background.run.after'));

        return $this->result;
    }
}
