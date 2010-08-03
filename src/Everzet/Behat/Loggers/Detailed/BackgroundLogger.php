<?php

namespace Everzet\Behat\Loggers\Detailed;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Base\BackgroundLogger as BaseLogger;

class BackgroundLogger extends BaseLogger
{
    protected $background;
    protected $output;
    protected $maxLineLength = 0;
    protected $helper;

    protected function setup(Container $container)
    {
        $this->background   = $this->runner->getSubject();
        $this->output       = $container->getParameter('output');
        $this->helper       = new Helper();
        $this->maxLineLength = $this->helper->calcStepsMaxLength($this->background);
    }

    public function getMaxLineLength()
    {
        return $this->maxLineLength;
    }

    public function printable()
    {
        return null === $this->scenarioLogger;
    }

    public function before()
    {
        if ($this->printable()) {
            $description = sprintf("  %s:%s",
                $this->background->getI18n()->__('background', 'Background'),
                $this->background->getTitle() ? ' ' . $this->background->getTitle() : ''
            );
            $this->output->write($description);
            $this->output->writeln($this->helper->getLineSourceComment(
                mb_strlen($description),
                $this->maxLineLength,
                strtr($this->background->getFile(), array(
                    $this->container->getParameter('features.path') . '/' => ''
                )),
                $this->background->getLine()
            ));
        }
    }

    public function after()
    {
        if ($this->printable()) {
            $this->output->writeln('');
        }
    }
}
