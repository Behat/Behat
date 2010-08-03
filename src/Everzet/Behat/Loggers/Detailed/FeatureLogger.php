<?php

namespace Everzet\Behat\Loggers\Detailed;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Base\FeatureLogger as BaseLogger;
use \Everzet\Behat\Runners\BackgroundRunner;
use \Everzet\Behat\Loggers\Detailed\BackgroundLogger;

class FeatureLogger extends BaseLogger
{
    protected $feature;
    protected $output;
    protected $helper;
    protected $maxLineLength = 0;

    protected function setup(Container $container)
    {
        $this->feature  = $this->runner->getSubject();
        $this->output   = $container->getParameter('output');
        $this->helper   = new Helper();
    }

    public function getMaxLineLength()
    {
        return $this->maxLineLength;
    }

    public function setMaxLineLength($length)
    {
        $this->maxLineLength = $length;
    }

    public function before()
    {
        if ($this->feature->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>",
                $this->helper->getTagsString($this->feature)
            ));
        }
        $this->output->writeln(sprintf("%s: %s",
            $this->feature->getI18n()->__('feature', 'Feature'),
            $this->feature->getTitle()
        ));
        foreach ($this->feature->getDescription() as $description) {
            $this->output->writeln(sprintf('  %s', $description));
        }
        $this->output->writeln('');

        if ($this->feature->hasBackground()) {
            $logger = new BackgroundLogger(new BackgroundRunner(
                $this->feature->getBackground(),
                $this->container->getSteps_LoaderService(),
                $this->container
            ), $this->container);
            $logger->run();
        }
    }

    public function after()
    {
        $this->output->writeln('');
    }
}
