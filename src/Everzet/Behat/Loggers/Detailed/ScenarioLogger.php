<?php

namespace Everzet\Behat\Loggers\Detailed;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Base\ScenarioLogger as BaseLogger;

class ScenarioLogger extends BaseLogger
{
    protected $scenario;
    protected $output;
    protected $maxLineLength = 0;
    protected $helper;

    protected function setup(Container $container)
    {
        $this->scenario = $this->runner->getSubject();
        $this->output   = $container->getParameter('output');
        $this->helper   = new Helper();
        $this->maxLineLength = $this->helper->calcStepsMaxLength($this->scenario);
    }

    public function getMaxLineLength()
    {
        return $this->maxLineLength;
    }

    public function before()
    {
        if (!$this->inOutline()) {
            if ($this->scenario->hasTags()) {
                $this->output->writeln(sprintf("<tag>%s</tag>",
                    $this->helper->getTagsString($this->scenario)
                ));
            }
            $description = sprintf("  %s:%s",
                $this->scenario->getI18n()->__('scenario', 'Scenario'),
                $this->scenario->getTitle() ? ' ' . $this->scenario->getTitle() : ''
            );

            $this->output->write($description);
            $this->output->writeln($this->helper->getLineSourceComment(
                mb_strlen($description),
                $this->maxLineLength,
                strtr($this->scenario->getFile(), array(
                    $this->container->getParameter('features.path') . '/' => ''
                )),
                $this->scenario->getLine()
            ));
        }
    }

    public function after()
    {
        if ($this->inOutline()) {
            $this->outlineLogger->afterScenario($this);
        } else {
            $this->output->writeln('');
        }
    }
}
