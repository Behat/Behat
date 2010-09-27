<?php

namespace Everzet\Behat\RunableNode;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\FeatureNode as BaseNode;

class FeatureNode extends BaseNode implements RunableNodeInterface
{
    protected $result = 0;

    public function getResult()
    {
        return $this->result;
    }

    public function run(Container $container)
    {
        $dispatcher = $container->getEventDispatcherService();

        $this->result = 0;

        $dispatcher->notify(new Event($this, 'feature.run.before'));

        // Filter scenarios
        $event = new Event($this, 'feature.run.filter_scenarios');
        $dispatcher->filter($event, $this->getScenarios());

        foreach ($event->getReturnValue() as $scenario) {
            $this->result = max($this->result, $scenario->run($container));
        }

        $dispatcher->notify(new Event($this, 'feature.run.after'));

        return $this->result;
    }
}
