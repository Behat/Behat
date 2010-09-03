<?php

namespace Everzet\Behat\Logger;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\SectionElement;
use Everzet\Gherkin\Element\Inline\PyStringElement;
use Everzet\Gherkin\Element\Inline\TableElement;

use Everzet\Behat\Runner\FeatureRunner;
use Everzet\Behat\Runner\ScenarioOutlineRunner;
use Everzet\Behat\Runner\ScenarioRunner;
use Everzet\Behat\Runner\BackgroundRunner;
use Everzet\Behat\Runner\StepRunner;

class DetailedLogger implements LoggerInterface
{
    protected $container;
    protected $output;
    protected $verbose;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->output       = $container->getParameter('output');
        $this->verbose      = $container->getParameter('logger.verbose');

        $this->output->setStyle('failed',      array('fg' => 'red'));
        $this->output->setStyle('undefined',   array('fg' => 'yellow'));
        $this->output->setStyle('pending',     array('fg' => 'yellow'));
        $this->output->setStyle('passed',      array('fg' => 'green'));
        $this->output->setStyle('skipped',     array('fg' => 'cyan'));
        $this->output->setStyle('comment',     array('fg' => 'black'));
        $this->output->setStyle('tag',         array('fg' => 'cyan'));
    }

    public function beforeFeature(FeatureRunner $runner)
    {
        $feature = $runner->getFeature();

        // Print tags if had ones
        if ($feature->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>", $this->getTagsString($feature)));
        }

        // Print feature title
        $this->output->writeln(sprintf("%s: %s",
            $feature->getI18n()->__('feature', 'Feature'),
            $feature->getTitle()
        ));

        // Print feature description
        foreach ($feature->getDescription() as $description) {
            $this->output->writeln(sprintf('  %s', $description));
        }
        $this->output->writeln('');

        // Run fake background to test if it runs without errors & prints it output
        if ($feature->hasBackground()) {
            $runner = new BackgroundRunner(
                $feature->getBackground()
              , $this->container->getSteps_LoaderService()
              , $this->container
              , $this
            );
            $runner->run();
        }
    }

    public function afterFeature(FeatureRunner $runner)
    {
    }

    public function beforeScenarioOutline(ScenarioOutlineRunner $runner)
    {
        $outline = $runner->getScenarioOutline();

        // Print tags if had ones
        if ($outline->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>", $this->getTagsString($outline)));
        }

        // Print outline description
        $description = sprintf("  %s:%s",
            $outline->getI18n()->__('scenario-outline', 'Scenario Outline'),
            $outline->getTitle() ? ' ' . $outline->getTitle() : ''
        );
        $this->output->writeln($description);
    }

    public function afterScenarioOutline(ScenarioOutlineRunner $runner)
    {
        $this->output->writeln('');
    }

    public function beforeScenario(ScenarioRunner $runner)
    {
        $scenario = $runner->getScenario();

        if (!$runner->isInOutline()) {
            // Print tags if had ones
            if ($scenario->hasTags()) {
                $this->output->writeln(sprintf("  <tag>%s</tag>",
                    $this->getTagsString($scenario)
                ));
            }

            // Print scenario description
            $description = sprintf("  %s:%s",
                $scenario->getI18n()->__('scenario', 'Scenario'),
                $scenario->getTitle() ? ' ' . $scenario->getTitle() : ''
            );
            $this->output->writeln($description);
        }
    }

    public function afterScenario(ScenarioRunner $runner)
    {
        $scenario = $runner->getScenario();

        if (!$runner->isInOutline()) {
            $this->output->writeln('');
        } else {
            $outlineRunner  = $runner->getCaller();
            $outline        = $outlineRunner->getScenarioOutline();
            $examples       = $outline->getExamples()->getTable();

            // Print outline description with steps & examples after first scenario in batch runned
            if (0 === $outlineRunner->key()) {

                // Print outline steps
                foreach ($runner->getStepRunners() as $stepRunner) {
                    $step = $stepRunner->getStep();

                    $description = sprintf('    %s %s', $step->getType(), $step->getText());
                    $this->output->write(sprintf("\033[36m%s\033[0m", $description), false, 1);
                    $this->output->writeln('');
                }

                // Print outline examples title
                $this->output->writeln(sprintf("\n    %s:",
                    $outline->getI18n()->__('examples', 'Examples')
                ));

                // Draw outline examples header row
                $this->output->writeln(preg_replace(
                    '/|([^|]*)|/', '<skipped>$1</skipped>', '      ' . $examples->getKeysAsString()
                ));
            }

            // Draw current scenario results row
            $this->output->writeln(preg_replace(
                '/|([^|]*)|/'
              , sprintf('<%s>$1</%s>', $runner->getStatus(), $runner->getStatus())
              , '      ' . $examples->getRowAsString($outlineRunner->key())
            ));

            // Print errors
            foreach ($runner->getExceptions() as $exception) {
                if ($this->verbose) {
                    $error = (string) $exception;
                } else {
                    $error = $exception->getMessage();
                }
                $this->output->writeln(sprintf("        <failed>%s</failed>",
                    strtr($error, array(
                        "\n"    =>  "\n      "
                      , "<"     =>  "["
                      , ">"     =>  "]"
                    ))
                ));
            }
        }
    }

    public function beforeBackground(BackgroundRunner $runner)
    {
        if (null === $runner->getCaller()) {
            $background = $runner->getBackground();

            $description = sprintf("  %s:%s",
                $background->getI18n()->__('background', 'Background'),
                $background->getTitle() ? ' ' . $background->getTitle() : ''
            );
            $this->output->writeln($description);
        }
    }

    public function afterBackground(BackgroundRunner $runner)
    {
        if (null === $runner->getCaller()) {
            $this->output->writeln('');
        }
    }

    public function beforeStep(StepRunner $runner)
    {
    }

    public function afterStep(StepRunner $runner)
    {
        if (
            // Not in scenario background
            !(null !== $runner->getCaller() &&
              $runner->getCaller() instanceof BackgroundRunner &&
              null !== $runner->getCaller()->getCaller() &&
              $runner->getCaller()->getCaller() instanceof ScenarioRunner) &&

            // Not in outline
            !(null !== $runner->getCaller() &&
              null !== $runner->getCaller()->getCaller() &&
              $runner->getCaller()->getCaller() instanceof ScenarioOutlineRunner)
           ) {
            $step = $runner->getStep();

            // Print step description
            $description = sprintf('    %s %s',
                $step->getType(), $step->getText($runner->getTokens())
            );
            $this->output->writeln(sprintf('<%s>%s</%s>',
                $runner->getStatus(), $description, $runner->getStatus()
            ));

            // Draw step arguments
            if ($step->hasArguments()) {
                foreach ($step->getArguments() as $argument) {
                    if ($argument instanceof PyStringElement) {
                        $this->output->writeln(sprintf("<%s>%s</%s>",
                            $runner->getStatus(),
                            $this->getPyString($argument, 6),
                            $runner->getStatus()
                        ));
                    } elseif ($argument instanceof TableElement) {
                        $this->output->writeln(sprintf("<%s>%s</%s>",
                            $runner->getStatus(),
                            $this->getTableString($argument, 6),
                            $runner->getStatus()
                        ));
                    }
                }
            }

            // Print step exception
            if (null !== $runner->getException()) {
                if ($this->verbose) {
                    $error = (string) $runner->getException();
                } else {
                    $error = $runner->getException()->getMessage();
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

    /**
     * Returns formatted tag string, prepared for console output
     *
     * @param   Section $section    section instance
     * 
     * @return  string
     */
    protected function getTagsString(SectionElement $section)
    {
        $tags = array();
        foreach ($section->getTags() as $tag) {
            $tags[] = '@' . $tag;
        }

        return implode(' ', $tags);
    }

    /**
     * Returns formatted PyString, prepared for console output
     *
     * @param   PyString    $pystring   PyString instance
     * @param   integer     $indent     indentation spaces count
     * 
     * @return  string
     */
    protected function getPyString(PyStringElement $pystring, $indent = 6)
    {
        return strtr(
            sprintf("%s\"\"\"\n%s\n\"\"\"", str_repeat(' ', $indent), (string) $pystring),
            array("\n" => "\n" . str_repeat(' ', $indent))
        );
    }

    /**
     * Returns formatted Table, prepared for console output
     *
     * @param   Table       $table      Table instance
     * @param   string      $indent     indentation spaces count
     * 
     * @return  string
     */
    protected function getTableString(TableElement $table, $indent = 6)
    {
        return strtr(
            sprintf(str_repeat(' ', $indent).'%s', $table),
            array("\n" => "\n".str_repeat(' ', $indent))
        );
    }
}
