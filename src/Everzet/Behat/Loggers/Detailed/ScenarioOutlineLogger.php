<?php

namespace Everzet\Behat\Loggers\Detailed;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Base\ScenarioOutlineLogger as BaseLogger;

class ScenarioOutlineLogger extends BaseLogger
{
    protected $outline;
    protected $output;
    protected $helper;
    protected $examples;
    protected $maxLineLength = 0;
    protected $verbose;

    protected $scenarioNumber = 0;

    protected function setup(Container $container)
    {
        $this->outline  = $this->runner->getSubject();
        $this->output   = $container->getParameter('output');
        $this->helper   = new Helper();
        $this->examples = $this->outline->getExamples()->getTable();
        $this->verbose  = $this->container->getParameter('logger.verbose');
        $this->maxLineLength = $this->helper->calcStepsMaxLength($this->outline);
    }

    public function getMaxLineLength()
    {
        return $this->maxLineLength;
    }

    public function before()
    {
        if ($this->outline->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>",
                $this->helper->getTagsString($this->outline)
            ));
        }
        $description = sprintf("  %s:%s",
            $this->outline->getI18n()->__('scenario-outline', 'Scenario Outline'),
            $this->outline->getTitle() ? ' ' . $this->outline->getTitle() : ''
        );

        $this->output->write($description);
        $this->output->writeln($this->helper->getLineSourceComment(
            mb_strlen($description),
            $this->maxLineLength,
            strtr($this->outline->getFile(), array(
                $this->container->getParameter('features.path') . '/' => ''
            )),
            $this->outline->getLine()
        ));
    }

    public function afterScenario(ScenarioLogger $logger)
    {
        if (0 === $this->scenarioNumber) {
            foreach ($logger->getStepLoggers() as $stepLogger) {
                $runner = $stepLogger->getRunner();
                $description = sprintf('    %s %s',
                    $runner->getSubject()->getType(), $runner->getSubject()->getText()
                );
                $this->output->write(sprintf("\033[36m%s\033[0m", $description), false, 1);
                if (null !== $runner->getDefinition()) {
                    $this->output->writeln($this->helper->getLineSourceComment(
                        mb_strlen($description),
                        $this->getMaxLineLength(),
                        strtr($runner->getDefinition()->getFile(), array(
                            $this->container->getParameter('features.path') . '/' => ''
                        )),
                        $runner->getDefinition()->getLine()
                    ));
                } else {
                    $this->output->writeln('');
                }
            }

            $this->output->writeln(sprintf("\n    %s:",
                $this->outline->getI18n()->__('examples', 'Examples')
            ));

            $this->output->writeln(preg_replace(
                '/|([^|]*)|/',
                '<skipped>$1</skipped>',
                '      ' . $this->examples->getKeysAsString()
            ));
        }
        $this->output->writeln(preg_replace(
            '/|([^|]*)|/',
            sprintf('<%s>$1</%s>', $logger->getStatus(), $logger->getStatus()),
            '      ' . $this->examples->getRowAsString($this->scenarioNumber)
        ));
        foreach ($logger->getExceptions() as $exception) {
            if ($this->verbose) {
                $error = (string) $exception;
            } else {
                $error = $exception->getMessage();
            }
            $this->output->writeln(sprintf("      <failed>%s</failed>",
                strtr($error, array(
                    "\n"    =>  "\n      ",
                    "<"     =>  "[",
                    ">"     =>  "]"
                ))
            ));
        }
        ++$this->scenarioNumber;
    }
}
