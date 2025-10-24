<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Hook\Call\AfterFeature;
use Behat\Behat\Hook\Call\BeforeFeature;
use Behat\Behat\Hook\Scope\StepScope;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Call\AfterSuite;
use Behat\Testwork\Hook\Call\BeforeSuite;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

final class JSONSetupPrinter implements SetupPrinter
{
    public function __construct(
        private readonly ExceptionPresenter $exceptionPresenter,
    ) {
    }

    public function printSetup(Formatter $formatter, Setup $setup): void
    {
        if (!$setup->isSuccessful() && $setup instanceof HookedSetup) {
            $this->handleHookCalls($formatter, $setup->getHookCallResults(), 'setup');
        }
    }

    public function printTeardown(Formatter $formatter, Teardown $teardown): void
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
                assert($outputPrinter instanceof JSONOutputPrinter);

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

                if ($callee instanceof BeforeSuite || $callee instanceof AfterSuite) {
                    $outputPrinter->addSuiteChild('failures', $attributes);
                } elseif ($callee instanceof BeforeFeature || $callee instanceof AfterFeature) {
                    $outputPrinter->addFeatureChild('failures', $attributes);
                } else {
                    $outputPrinter->addScenarioChild('failures', $attributes);
                }
            }
        }
    }
}
