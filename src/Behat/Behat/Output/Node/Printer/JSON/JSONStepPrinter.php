<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;

final class JSONStepPrinter implements StepPrinter
{
    public function __construct(
        private readonly ExceptionPresenter $exceptionPresenter,
    ) {
    }

    public function printStep(
        Formatter $formatter,
        Scenario $scenario,
        StepNode $step,
        StepResult $result,
    ): void {
        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);

        $message = $step->getKeyword() . ' ' . $step->getText();

        if ($result instanceof ExceptionResult && $result->hasException()) {
            $message .= ': ' . $this->exceptionPresenter->presentException(
                $result->getException(),
                applyEditorUrl: false
            );
        }

        $attributes = ['message' => $message];

        $outputPrinter->addCurrentScenarioAttributes(['line' => $step->getLine()]);

        switch ($result->getResultCode()) {
            case TestResult::FAILED:
                $attributes['type'] = 'failed';
                $outputPrinter->addScenarioChild('failures', $attributes);
                break;

            case TestResult::PENDING:
                $attributes['type'] = 'pending';
                $outputPrinter->addScenarioChild('failures', $attributes);
                break;

            case TestResult::UNDEFINED:
                $attributes['type'] = 'undefined';
                $outputPrinter->addScenarioChild('failures', $attributes);
                break;
        }
    }
}
