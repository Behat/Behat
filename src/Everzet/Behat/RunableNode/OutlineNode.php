<?php

namespace Everzet\Behat\RunableNode;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\OutlineNode as BaseNode;

class OutlineNode extends BaseNode implements RunableNodeInterface
{
    protected $result = 0;
    protected $finishedScenarios = 0;

    public function getResult()
    {
        return $this->result;
    }

    public function getFinishedScenariosCount()
    {
        return $this->finishedScenarios;
    }

    public function run(Container $container)
    {
        $dispatcher = $container->getEventDispatcherService();

        $this->result = 0;
        $this->finishedScenarios = 0;

        $dispatcher->notify(new Event($this, 'outline.run.before'));

        foreach ($this->getExamples()->getTable()->getHash() as $tokens) {
            $scenario = new ScenarioNode($this->getLine(), $this->getI18n(), $this->getFile());
            $scenario->setFeature($this->getFeature());
            $scenario->setOutline($this);
            $scenario->addSteps($this->getSteps());

            $this->result = max($this->result, $scenario->run($container, $tokens));
            ++$this->finishedScenarios;
        }

        $dispatcher->notify(new Event($this, 'outline.run.after'));

        return $this->result;
    }
}
