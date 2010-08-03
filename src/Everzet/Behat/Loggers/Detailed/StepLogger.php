<?php

namespace Everzet\Behat\Loggers\Detailed;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Gherkin\Structures\Inline\PyString;
use \Everzet\Gherkin\Structures\Inline\Table;
use \Everzet\Gherkin\Structures\Inline\Examples;

use \Everzet\Behat\Loggers\Detailed\BackgroundLogger;

use \Everzet\Behat\Loggers\Base\StepLogger as BaseLogger;

class StepLogger extends BaseLogger
{
    protected $step;
    protected $output;
    protected $helper;
    protected $verbose;

    protected function setup(Container $container)
    {
        $this->step     = $this->runner->getSubject();
        $this->output   = $container->getParameter('output');
        $this->verbose  = $this->container->getParameter('logger.verbose');
        $this->helper   = new Helper();
    }

    public function printable()
    {
        $parent = $this->scenarioLogger;

        return (!($parent instanceof BackgroundLogger) || $parent->printable()) &&
               (!($parent instanceof ScenarioLogger) || !$parent->inOutline());
    }

    public function after()
    {
        if ($this->printable()) {
            $description = sprintf('    %s %s',
                $this->step->getType(), $this->step->getText($this->runner->getTokens())
            );
            $this->output->write(sprintf('<%s>%s</%s>',
                $this->getStatus(), $description, $this->getStatus()
            ));
            if (null !== $this->runner->getDefinition()) {
                $this->output->writeln($this->helper->getLineSourceComment(
                    mb_strlen($description),
                    $this->scenarioLogger->getMaxLineLength(),
                    strtr($this->runner->getDefinition()->getFile(), array(
                        $this->container->getParameter('features.path') . '/' => ''
                    )),
                    $this->runner->getDefinition()->getLine()
                ));
            } else {
                $this->output->writeln('');
            }

            if ($this->step->hasArguments()) {
                foreach ($this->step->getArguments() as $argument) {
                    if ($argument instanceof PyString) {
                        $this->output->writeln(sprintf("<%s>%s</%s>",
                            $this->getStatus(),
                            $this->helper->getPyString($argument, 6),
                            $this->getStatus()
                        ));
                    } elseif ($argument instanceof Table) {
                        $this->output->writeln(sprintf("<%s>%s</%s>",
                            $this->getStatus(),
                            $this->helper->getTableString($argument, 6),
                            $this->getStatus()
                        ));
                    }
                }
            }

            if (null !== $this->getException()) {
                if ($this->verbose) {
                    $error = (string) $this->getException();
                } else {
                    $error = $this->getException()->getMessage();
                }
                $this->output->writeln(sprintf("      <failed>%s</failed>",
                    strtr($error, array(
                        "\n"    =>  "\n      ",
                        "<"     =>  "[",
                        ">"     =>  "]"
                    ))
                ));
            }
        }
    }
}
