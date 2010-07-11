<?php

namespace BehaviorTester\Console\Commands;

use \Symfony\Components\Console\Command\Command;
use \Symfony\Components\Console\Input\InputInterface;
use \Symfony\Components\Console\Input\InputArgument;
use \Symfony\Components\Console\Input\InputOption;
use \Symfony\Components\Console\Output\OutputInterface;
use \Symfony\Components\Finder\Finder;

use \BehaviorTester\StepsDefinition;
use \BehaviorTester\Exceptions\Redundant;
use \BehaviorTester\Exceptions\Ambiguous;
use \BehaviorTester\Exceptions\Undefined;

use \Gherkin\Parser;
use \Gherkin\Feature;
use \Gherkin\Scenario;
use \Gherkin\ScenarioOutline;

class Test extends Command
{
    protected $basePath;
    protected $finished = array();

    protected $featureNum = -1;
    protected $scenarioNum = -1;

    protected function startNewFeature()
    {
        $this->featureNum++;
        $this->scenarioNum = -1;

        $this->finished[$this->featureNum] = array();
    }

    protected function startNewScenario()
    {
        $this->scenarioNum++;

        $this->finished[$this->featureNum][$this->scenarioNum] = array(
            'failed'    => array(),
            'passed'    => array(),
            'skipped'   => array(),
            'pending'   => array(),
            'undefined' => array()
        );
    }

    protected function logStep($step, $status)
    {
        $this->finished[$this->featureNum][$this->scenarioNum][$status][] = $step;
    }

    protected function hasFailedStepsInCurrentScenario()
    {
        return count($this->finished[$this->featureNum][$this->scenarioNum]['failed']);
    }

    protected function configure()
    {
        $this->setName('test');

        $this->setDefinition(array(
            new InputArgument('features', InputArgument::OPTIONAL, 'Features folder', './features')
        ));
    }

    protected function ltrimPaths($message)
    {
        return strtr($message, array($this->basePath . '/' => ''));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->basePath = realpath(dirname($input->getArgument('features')));

        // Read steps definition from files
        $finder = new Finder();
        $files = $finder->files()->name('*.php')->in($this->basePath . '/steps');
        $steps = new StepsDefinition();
        try {
            foreach ($files as $file) {
                require $file;
            }
        } catch (Redundant $e) {
            $output->writeln(sprintf("<failed>%s</failed>\n", $this->ltrimPaths($e)));
        }

        // Read feature files
        $finder = new Finder();
        $files = $finder->files()->name('*.feature')->in($input->getArgument('features'));

        $parser = new Parser();
        foreach ($files as $file) {
            $feature = $parser->parse(file_get_contents($file));

            $this->startNewFeature();
            $output->writeln(sprintf("Feature: %s  <comment>#%s</comment>",
                $feature->getTitle(), $this->ltrimPaths(realpath($file))
            ));
            foreach ($feature->getDescription() as $description) {
                $output->writeln(sprintf('  %s', $description));
            }
            $output->writeln('');

            foreach ($feature->getScenarios() as $scenario) {
                $this->startNewScenario();
                if ($scenario instanceof \Gherkin\ScenarioOutline) {
                    $output->writeln(sprintf('    <passed>Scenario Outline: %s</passed>',
                        $scenario->getTitle()
                    ));

                    foreach ($scenario->getExamples() as $values) {
                        $this->runScenario($scenario, $steps, $output, $values);
                    }
                } else {
                    $output->writeln(sprintf('    <passed>Scenario: %s</passed>',
                        $scenario->getTitle()
                    ));

                    $this->runScenario($scenario, $steps, $output);
                }
            }
        }
    }

    protected function runScenario(Scenario $scenario, StepsDefinition $steps,
                                   OutputInterface $output, array $values = array())
    {
        foreach ($scenario->getSteps() as $step) {
            try {
                try {
                    $definition = $steps->findDefinition($step, $values);
                } catch (Ambiguous $e) {
                    $this->logStep($step, 'failed');
                    $output->writeln(sprintf("      <failed>%s %s</failed>",
                        $step->getType(),
                        $step->getText()
                    ));
                    $output->writeln(sprintf("        <failed>%s</failed>",
                        strtr($this->ltrimPaths($e), array("\n" => "\n        "))
                    ));
                }
            } catch (Undefined $e) {
                $this->logStep($step, 'undefined');
                $output->writeln(sprintf("      <undefined>%s %s</undefined>",
                    $step->getType(),
                    $step->getText()
                ));
            }

            if ($this->hasFailedStepsInCurrentScenario()) {
                $output->writeln(sprintf("      <skipped>%s %s</skipped>  <comment>#%s</comment>",
                    $definition['type'],
                    $definition['description'],
                    $this->ltrimPaths($definition['file']) . ':' . $definition['line']
                ));
            } else {
                try {
                    call_user_func_array($definition['callback'], $definition['values']);
                    $this->logStep($definition, 'passed');
                    $output->writeln(sprintf("      <passed>%s %s</passed>  <comment>#%s</comment>",
                        $definition['type'],
                        $definition['description'],
                        $this->ltrimPaths($definition['file']) . ':' . $definition['line']
                    ));
                } catch (\Exception $e) {
                    $this->logStep($definition, 'failed');
                    $output->writeln(sprintf("      <failed>%s %s</failed>  <comment>#%s</comment>",
                        $definition['type'],
                        $definition['description'],
                        $this->ltrimPaths($definition['file']) . ':' . $definition['line']
                    ));
                    $output->writeln(sprintf("        <failed>%s</failed>",
                        strtr($e, array("\n" => "\n        "))
                    ));
                }
            }
        }

        $output->writeln('');
    }
}
