<?php
namespace Behat\Behat\Output\Node\Printer\JUnit;

use Behat\Behat\Hook\Scope\StepScope;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Call\HookCall;
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

    /** @var ExceptionPresenter */
    private $exceptionPresenter;

    public function __construct(ExceptionPresenter $exceptionPresenter)
    {
        $this->exceptionPresenter = $exceptionPresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function printSetup(Formatter $formatter, Setup $setup)
    {
        if (!$setup->isSuccessful()) {
            if ($setup instanceof HookedSetup) {
                $this->handleHookCalls($formatter, $setup->getHookCallResults(), 'setup');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function printTeardown(Formatter $formatter, Teardown $teardown)
    {
        if (!$teardown->isSuccessful()) {
            if ($teardown instanceof HookedTeardown) {
                $this->handleHookCalls($formatter, $teardown->getHookCallResults(), 'teardown');
            }
        }
    }

    /**
     * @param Formatter $formatter
     * @param CallResults $results
     * @param string $messageType
     */
    private function handleHookCalls(Formatter $formatter, CallResults $results, $messageType)
    {
        /** @var CallResult $hookCallResult */
        foreach ($results as $hookCallResult) {
            if ($hookCallResult->hasException()) {
                /** @var HookCall $call */
                $call = $hookCallResult->getCall();
                $scope = $call->getScope();
                /** @var JUnitOutputPrinter $outputPrinter */
                $outputPrinter = $formatter->getOutputPrinter();

                $message = '';
                if ($scope instanceof StepScope) {
                    $message .= $scope->getStep()->getKeyword() . ' ' . $scope->getStep()->getText() . ': ';
                }
                $message .= $this->exceptionPresenter->presentException($hookCallResult->getException());

                $attributes = array(
                    'message' => $message,
                    'type'    => $messageType,
                );

                $outputPrinter->addTestcaseChild('failure', $attributes);

            }
        }
    }
}
