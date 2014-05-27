<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;

/**
 * Calculates width of scenario. Width of scenario = max width of scenario title and scenario step texts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WidthCalculator
{
    /**
     * Calculates scenario width.
     *
     * @param Scenario $scenario
     * @param integer  $indentation
     * @param integer  $subIndentation
     *
     * @return integer
     */
    public function calculateScenarioWidth(Scenario $scenario, $indentation, $subIndentation)
    {
        $length = $this->calculateScenarioHeaderWidth($scenario, $indentation);

        foreach ($scenario->getSteps() as $step) {
            $stepLength = $this->calculateStepWidth($step, $indentation + $subIndentation);
            $length = max($length, $stepLength);
        }

        return $length;
    }

    /**
     * Calculates outline examples width.
     *
     * @param ExampleNode $example
     * @param integer     $indentation
     * @param integer     $subIndentation
     *
     * @return integer
     */
    public function calculateExampleWidth(ExampleNode $example, $indentation, $subIndentation)
    {
        $length = $this->calculateScenarioHeaderWidth($example, $indentation);

        foreach ($example->getSteps() as $step) {
            $stepLength = $this->calculateStepWidth($step, $indentation + $subIndentation);
            $length = max($length, $stepLength);
        }

        return $length;
    }

    /**
     * Calculates scenario header width.
     *
     * @param Scenario $scenario
     * @param integer  $indentation
     *
     * @return integer
     */
    public function calculateScenarioHeaderWidth(Scenario $scenario, $indentation)
    {
        $indentText = str_repeat(' ', intval($indentation));

        if ($scenario instanceof ExampleNode) {
            $header = sprintf('%s%s', $indentText, $scenario->getTitle());
        } else {
            $title = $scenario->getTitle();
            $lines = explode("\n", $title);
            $header = sprintf('%s%s: %s', $indentText, $scenario->getKeyword(), array_shift($lines));
        }

        return mb_strlen(rtrim($header), 'utf8');
    }

    /**
     * Calculates step width.
     *
     * @param StepNode $step
     * @param integer  $indentation
     *
     * @return integer
     */
    public function calculateStepWidth(StepNode $step, $indentation)
    {
        $indentText = str_repeat(' ', intval($indentation));

        $text = sprintf('%s%s %s', $indentText, $step->getKeyword(), $step->getText());

        return mb_strlen($text, 'utf8');
    }
}
