<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JUnit;

use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Prints step with optional results.
 *
 * @author Wouter J <wouter@wouterj.nl>
 * @author James Watson <james@sitepulse.org>
 */
class JUnitStepPrinter implements StepPrinter
{
    public function __construct(
        private readonly ExceptionPresenter $exceptionPresenter,
    ) {
    }

    /**
     * Prints step using provided printer.
     */
    public function printStep(Formatter $formatter, Scenario $scenario, StepNode $step, StepResult $result)
    {
        /** @var JUnitOutputPrinter $outputPrinter */
        $outputPrinter = $formatter->getOutputPrinter();

        $message = $step->getKeyword() . ' ' . $step->getText();

        if ($result instanceof ExceptionResult && $result->hasException()) {
            $message .= ': ' . $this->exceptionPresenter->presentException(
                $result->getException(),
                applyEditorUrl: false
            );
        }

        $attributes = ['message' => $message];

        $outputPrinter->addCurrentTestCaseAttributes(['line' => $step->getLine()]);

        switch ($result->getResultCode()) {
            case TestResult::FAILED:
                $outputPrinter->addTestcaseChild('failure', $attributes);
                break;

            case TestResult::PENDING:
                $attributes['type'] = 'pending';
                $outputPrinter->addTestcaseChild('error', $attributes);
                break;

            case TestResult::UNDEFINED:
                $attributes['type'] = 'undefined';
                $outputPrinter->addTestcaseChild('error', $attributes);
                break;
        }
    }
}
