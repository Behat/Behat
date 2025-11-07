<?php

namespace Behat\Behat\Output\Node\Printer\JUnit;

use Behat\Behat\Hook\Scope\StepScope;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * @author: Jakob Erdmann <jakob.erdmann@rocket-internet.com>
 */
class JUnitSetupPrinter implements SetupPrinter
{
    public function __construct(
        private readonly ExceptionPresenter $exceptionPresenter,
    ) {
    }

    public function printSetup(Formatter $formatter, Setup $setup)
    {
        if (!$setup->isSuccessful() && $setup instanceof HookedSetup) {
            $this->handleHookCalls($formatter, $setup->getHookCallResults(), 'setup');
        }
    }

    public function printTeardown(Formatter $formatter, Teardown $teardown)
    {
        if (!$teardown->isSuccessful() && $teardown instanceof HookedTeardown) {
            $this->handleHookCalls($formatter, $teardown->getHookCallResults(), 'teardown');
        }
    }

    private function handleHookCalls(Formatter $formatter, CallResults $results, string $messageType): void
    {
        foreach ($results as $hookCallResult) {
            if ($hookCallResult->hasException()) {
                $call = $hookCallResult->getCall();
                $scope = $call->getScope();
                $outputPrinter = $formatter->getOutputPrinter();
                assert($outputPrinter instanceof JUnitOutputPrinter);

                $callee = $call->getCallee();
                $message = $callee->getName();
                if ($scope instanceof StepScope) {
                    $message .= ': ' . $scope->getStep()->getKeyword() . ' ' . $scope->getStep()->getText();
                }
                $message .= ': ' . $this->exceptionPresenter->presentException(
                    $hookCallResult->getException(),
                    applyEditorUrl: false
                );

                $attributes = [
                    'message' => $message,
                    'type' => $messageType,
                ];

                $outputPrinter->addTestcaseChild('failure', $attributes);
            }
        }
    }
}
