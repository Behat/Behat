<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Output\Node\Printer\Pretty;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Tester\Result\BehatTestResult;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Output\Node\Printer\HookPrinter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;

/**
 * Behat pretty hook printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyHookPrinter implements HookPrinter
{
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;

    /**
     * Initializes printer.
     *
     * @param ExceptionPresenter $exceptionPresenter
     */
    public function __construct(ExceptionPresenter $exceptionPresenter)
    {
        $this->exceptionPresenter = $exceptionPresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function printHookResults(
        Formatter $formatter,
        $eventName,
        LifecycleEvent $hookedEvent,
        CallResults $callResults
    ) {
        foreach ($callResults as $result) {
            $this->printHookResult($formatter, $eventName, $result);
        }
    }

    private function printHookResult(Formatter $formatter, $eventName, CallResult $result)
    {
        if (!$result->hasStdOut() && !$result->hasException()) {
            return;
        }

        $printer = $formatter->getOutputPrinter();
        $indentText = '  ';
        $style = new BehatTestResult($result->hasException() ? BehatTestResult::FAILED : BehatTestResult::PASSED);
        $hook = $result->getCall()->getCallee();
        $name = (string)$hook;
        $path = $hook->getPath();

        $message = sprintf('%s{+%s}@%s{-%s} {+comment}# %s{-comment}', $indentText, $style, $name, $style, $path);

        if ($this->isBeforeHook($eventName)) {
            $printer->writeln($message);
            $printer->writeln(sprintf('%s{+%s}|{-%s}', $indentText, $style, $style));
            $this->printHookCallInformation($printer, $result, (string)$style, $indentText);
            $printer->writeln();
        } else {
            $printer->writeln(sprintf('%s{+%s}|{-%s}', $indentText, $style, $style));
            $this->printHookCallInformation($printer, $result, (string)$style, $indentText);
            $printer->writeln($message);
            $printer->writeln();
        }
    }

    private function printHookCallInformation(OutputPrinter $printer, CallResult $callResult, $style, $indentText)
    {
        if ($callResult->hasStdOut()) {
            $pad = function ($line) use ($style, $indentText) {
                return sprintf(
                    '%s{+%s}|{-%s}  {+stdout}%s{-stdout}', $indentText, $style, $style, $line
                );
            };
            $printer->writeln(
                implode("\n", array_map($pad, explode("\n", $callResult->getStdOut())))
            );
        }

        if ($callResult->hasException()) {
            $pad = function ($l) use ($style, $indentText) {
                return sprintf(
                    '%s{+%s}X{-%s}  {+exception}%s{-exception}', $indentText, $style, $style, $l
                );
            };
            $exception = $this->exceptionPresenter->presentException($callResult->getException());
            $printer->writeln(implode("\n", array_map($pad, explode("\n", $exception))));
        }

        $printer->writeln(sprintf('%s{+%s}|{-%s}', $indentText, $style, $style));
    }

    private function isBeforeHook($eventName)
    {
        switch ($eventName) {
            case SuiteTested::BEFORE:
            case FeatureTested::BEFORE:
            case ScenarioTested::BEFORE:
            case ExampleTested::BEFORE:
            case StepTested::BEFORE:
                return true;
        }

        return false;
    }
}
