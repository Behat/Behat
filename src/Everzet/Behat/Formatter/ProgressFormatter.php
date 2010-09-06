<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Runner\RunnerInterface;
use Everzet\Behat\Runner\ScenarioRunner;
use Everzet\Behat\Runner\BackgroundRunner;

class ProgressFormatter extends PrettyFormatter implements FormatterInterface
{
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('step.post_test',              array($this, 'printStep'));
        $dispatcher->connect('step.post_skip',              array($this, 'printStep'));

        $dispatcher->connect('suite.post_test',             array($this, 'printEmptyLine'));
        $dispatcher->connect('suite.post_test',             array($this, 'printFailedSteps'));
        $dispatcher->connect('suite.post_test',             array($this, 'printPendingSteps'));
        $dispatcher->connect('suite.post_test',             array($this, 'printStatistics'));
        $dispatcher->connect('suite.post_test',             array($this, 'printSnippets'));
    }

    public function printStep(Event $event)
    {
        $runner = $event->getSubject();

        switch ($runner->getStatus()) {
            case 'passed':
                $this->output->write('<passed>.</passed>');
                break;
            case 'skipped':
                $this->output->write('<skipped>-</skipped>');
                break;
            case 'pending':
                $this->output->write('<pending>P</pending>');
                break;
            case 'undefined':
                $this->output->write('<undefined>U</undefined>');
                break;
            case 'failed':
                $this->output->write('<failed>F</failed>');
                break;
        }
    }

    public function printEmptyLine(Event $event)
    {
        $this->output->writeln("\n");
    }

    public function printFailedSteps(Event $event)
    {
        $runner = $event->getSubject();

        if (count($stepRunners = $runner->getFailedStepRunners())) {
            $this->output->writeln("<failed>(::) failed steps (::)</failed>\n");

            foreach ($stepRunners as $number => $stepRunner) {
                $step = $stepRunner->getStep();

                // Print step exception
                if (null !== $stepRunner->getException()) {
                    if ($this->verbose) {
                        $error = (string) $stepRunner->getException();
                    } else {
                        $error = $stepRunner->getException()->getMessage();
                    }
                    $this->output->write(sprintf("%s. \033[31m%s\033[0m"
                      , str_pad((string) ($number + 1), 2, '0', STR_PAD_LEFT)
                      , strtr($error, array("\n" => "\n    "))
                    ), true, 1);
                }

                $this->printStepInformation($stepRunner, 'failed');
            }
        }
    }

    public function printPendingSteps(Event $event)
    {
        $runner = $event->getSubject();

        if (count($stepRunners = $runner->getPendingStepRunners())) {
            $this->output->writeln("<pending>(::) pending steps (::)</pending>\n");

            $number = 1;
            foreach ($stepRunners as $key => $stepRunner) {
                $step = $stepRunner->getStep();

                // Print step exception
                if (null !== $stepRunner->getException()) {
                    if ($this->verbose) {
                        $error = (string) $stepRunner->getException();
                    } else {
                        $error = $stepRunner->getException()->getMessage();
                    }
                    $this->output->write(sprintf("%s. \033[33m%s\033[0m"
                      , str_pad((string) $number++, 2, '0', STR_PAD_LEFT)
                      , strtr($error, array("\n" => "\n    "))
                    ), true, 1);
                }

                $this->printStepInformation($stepRunner, 'pending');
            }
        }
    }

    protected function printStepInformation(RunnerInterface $stepRunner, $type)
    {
        // Print step information
        $step = $stepRunner->getStep();
        $description = sprintf("    <%s>In step `%s %s'.</%s>"
          , $type
          , $step->getType()
          , $step->getText()
          , $type
        );
        $this->maxDescriptionLength = $this->maxDescriptionLength > mb_strlen($description)
            ? $this->maxDescriptionLength
            : mb_strlen($description);
        $this->output->write($description);
        $this->printLineSourceComment(
            mb_strlen($description)
          , $stepRunner->getDefinition()->getFile()
          , $stepRunner->getDefinition()->getLine()
        );

        // Print scenario information
        $parentRunner = $stepRunner->getParentRunner();
        if ($parentRunner instanceof BackgroundRunner) {
            $item           = $parentRunner->getBackground();
            $description    = sprintf("    <%s>From scenario background.</%s>"
              , $type
              , $type
            );
        } elseif ($parentRunner instanceof ScenarioRunner) {
            $item           = $parentRunner->getScenario();
            $description    = sprintf("    <%s>From scenario %s.</%s>"
              , $type
              , $item->getTitle() ? sprintf("`%s'", $item->getTitle()) : '***'
              , $type
            );
        }
        $this->maxDescriptionLength = $this->maxDescriptionLength > mb_strlen($description)
            ? $this->maxDescriptionLength
            : mb_strlen($description);
        $this->output->write($description);
        $this->printLineSourceComment(
            mb_strlen($description)
          , $item->getFile()
          , $item->getLine()
        );
        $this->output->writeln('');
    }
}
