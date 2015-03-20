<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JUnit;

use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\ExceptionResult;

/**
 * Prints step with optional results.
 *
 * @author Wouter J <wouter@wouterj.nl>
 * @author James Watson <james@sitepulse.org>
 */
class JUnitStepPrinter implements StepPrinter
{
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;

    public function __construct(ExceptionPresenter $exceptionPresenter)
    {
        $this->exceptionPresenter = $exceptionPresenter;
    }

    /**
     * Prints step using provided printer.
     *
     * @param Formatter  $formatter
     * @param Scenario   $scenario
     * @param StepNode   $step
     * @param StepResult $result
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result)
    {
        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();

        $message = $step->getKeyword() . ' ' . $step->getText();

        if ($result instanceof ExceptionResult && $result->hasException()) {
            $message .= ': ' . $this->exceptionPresenter->presentException($result->getException());
        }

        $attributes = array('message' => $message);

        switch ($result->getResultCode()) {
            case TestResult::FAILED:
                $outputPrinter->addTestcaseChild('failure', $attributes);
                break;

            case TestResult::PENDING:
                $attributes['type'] = 'pending';
                $outputPrinter->addTestcaseChild('error', $attributes);
                break;

            case StepResult::UNDEFINED:
                $attributes['type'] = 'undefined';
                $outputPrinter->addTestcaseChild('error', $attributes);
                break;
        }
    }
}
