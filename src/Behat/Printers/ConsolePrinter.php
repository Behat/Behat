<?php

namespace Behat\Printers;

use \Gherkin\Feature;
use \Gherkin\Background;
use \Gherkin\ScenarioOutline;
use \Gherkin\Scenario;

use \Symfony\Components\Console\Output\OutputInterface;

class ConsolePrinter implements Printer
{
    protected $output;
    protected $basePath;

    public function __construct(OutputInterface $output, $basePath)
    {
        $this->output = $output;
        $this->basePath = $basePath;
        $this->setColors();
    }

    protected function setColors()
    {
        $this->output->setStyle('failed',      array('fg' => 'red'));
        $this->output->setStyle('undefined',   array('fg' => 'yellow'));
        $this->output->setStyle('pending',     array('fg' => 'yellow'));
        $this->output->setStyle('passed',      array('fg' => 'green'));
        $this->output->setStyle('skipped',     array('fg' => 'cyan'));
        $this->output->setStyle('comment',     array('fg' => 'black'));
        $this->output->setStyle('tag',         array('fg' => 'cyan'));
    }

    protected function ltrimPaths($message)
    {
        return strtr($message, array($this->basePath . '/' => ''));
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

    public function logStep($code, $type, $text, $file = null,
                            $line = null, \Exception $e = null)
    {
        $status = sprintf('      <%s>%s</%s>', $code, $type . ' ' . $text, $code);
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
