<?php

namespace BehaviorTester\ResultPrinter;

class Text extends \BehaviorTester\ResultPrinter
{
    const COL_FAILED    = '31;10m';
    const COL_SKIPPED   = '33;10m';
    const COL_INCOMPL   = '36;10m';
    const COL_SUCCESS   = '32;10m';

    /**
     * Handler for 'start class' event.
     *
     * @param  string $name
     */
    protected function startClass($name)
    {
    }

    protected function writeColor($text, $color = '30;42m')
    {
        $this->write(sprintf("\x1b[%s\x1b[2K%s\x1b[0m\x1b[2K", $color, $text));
    }

    /**
     * Handler for 'on test' event.
     *
     * @param  string  $name
     * @param  boolean $success
     * @param  array   $steps
     */
    protected function onTest($name, $success = TRUE, \Gherkin\Feature $feature)
    {
        if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
            $featureStatus = 'failed';
        }

        else if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED) {
            $featureStatus = 'skipped';
        }

        else if ($this->testStatus == \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
            $featureStatus = 'incomplete';
        }

        else {
            $featureStatus = 'successful';
        }

        $this->write(sprintf("Feature: %s\n", $feature->getTitle()));
        if ($feature->hasDescription()) {
            $this->write(sprintf("  %s", implode("\n  ", $feature->getDescription()) . "\n"));
        }

        foreach ($feature->getScenarios() as $scenario) {
            $this->writeColor(
              sprintf("  Scenario: %s\n", $scenario->getTitle()),
              self::COL_SUCCESS
            );

            foreach ($scenario->getSteps() as $step)
            {
                $this->write(
                  sprintf(
                    "    %s %s\n",
                    $step->getType(),
                    $step->getText()
                  )
                );
            }
        }

        $this->write("\n");
    }

    /**
     * Handler for 'end run' event.
     *
     */
    protected function endRun()
    {
        $this->write("\x1b[30;42m\x1b[2K");
        $this->write(
          sprintf(
            "Scenarios: %d, Failed: %d, Skipped: %d, Incomplete: %d.\n",

            $this->successful + $this->failed +
            $this->skipped    + $this->incomplete,
            $this->failed,
            $this->skipped,
            $this->incomplete
          )
        );
    }
}
