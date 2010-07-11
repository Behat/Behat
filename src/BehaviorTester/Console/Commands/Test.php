<?php

namespace BehaviorTester\Console\Commands;

use \Symfony\Components\Console\Command\Command;
use \Symfony\Components\Console\Input\InputInterface;
use \Symfony\Components\Console\Input\InputArgument;
use \Symfony\Components\Console\Input\InputOption;
use \Symfony\Components\Console\Output\OutputInterface;
use \Symfony\Components\Finder\Finder;

use \Gherkin\Feature;
use \Gherkin\Background;
use \Gherkin\Scenario;
use \Gherkin\ScenarioOutline;

use \BehaviorTester\StepsDefinition;
use \BehaviorTester\FeatureRuner;
use \BehaviorTester\OutputLogger;

class Test extends Command implements OutputLogger
{
    protected $output;
    protected $basePath;

    protected function ltrimPaths($message)
    {
        return strtr($message, array($this->basePath . '/' => ''));
    }

    protected function configure()
    {
        $this->setName('test');

        $this->setDefinition(array(
            new InputArgument('features', InputArgument::OPTIONAL, 'Features folder', './features')
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
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

        foreach ($files as $file) {
            $runer = new FeatureRuner($file, $this, $steps);
            $runer->run();
            $output->writeln('');
        }
    }

    public function logFeature(Feature $feature, $file)
    {
        $this->output->writeln(sprintf("Feature: %s  <comment>#%s</comment>",
            $feature->getTitle(), $this->ltrimPaths(realpath($file))
        ));
        foreach ($feature->getDescription() as $description) {
            $this->output->writeln(sprintf('  %s', $description));
        }
    }

    public function logBackground(Background $background)
    {
        $this->output->writeln(sprintf("\n    <passed>Background: %s</passed>",
            $background->getTitle()
        ));
    }

    public function logScenarioOutline(ScenarioOutline $scenario)
    {
        $this->output->writeln(sprintf("\n    <passed>Scenario Outline: %s</passed>",
            $scenario->getTitle()
        ));
    }

    public function logScenario(Scenario $scenario)
    {
        $this->output->writeln(sprintf("\n    <passed>Scenario: %s</passed>",
            $scenario->getTitle()
        ));
    }

    public function logStep($code, $type, $definition, $file = null,
                            $line = null, \Exception $e = null)
    {
        $status = sprintf('      <%s>%s</%s>', $code, $type . ' ' . $definition, $code);
        $status = str_pad($status, 60 + (strlen($code) * 2));

        if (null !== $file && null !== $line) {
            $status .= sprintf('<comment>%s:%d</comment>',
                $this->ltrimPaths(realpath($file)), $line
            );
        }

        $this->output->writeln($status);

        if (null !== $e) {
            $this->output->writeln(sprintf("          <failed>%s</failed>",
                strtr($this->ltrimPaths($e), array("\n" => "\n        "))
            ));
        }
    }
}
