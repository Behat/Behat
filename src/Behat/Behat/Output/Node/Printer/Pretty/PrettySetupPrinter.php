<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Prints hooks in a pretty fashion.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PrettySetupPrinter implements SetupPrinter
{
    /**
     * @var ResultToStringConverter
     */
    private $resultConverter;
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;
    /**
     * @var string
     */
    private $indentText;
    /**
     * @var bool
     */
    private $newlineBefore;
    /**
     * @var bool
     */
    private $newlineAfter;

    /**
     * Initializes printer.
     *
     * @param ResultToStringConverter $resultConverter
     * @param ExceptionPresenter      $exceptionPresenter
     * @param integer                 $indentation
     * @param bool                 $newlineBefore
     * @param bool                 $newlineAfter
     */
    public function __construct(
        ResultToStringConverter $resultConverter,
        ExceptionPresenter $exceptionPresenter,
        $indentation = 0,
        $newlineBefore = false,
        $newlineAfter = false
    ) {
        $this->resultConverter = $resultConverter;
        $this->exceptionPresenter = $exceptionPresenter;
        $this->indentText = str_repeat(' ', intval($indentation));
        $this->newlineBefore = $newlineBefore;
        $this->newlineAfter = $newlineAfter;
    }

    /**
     * {@inheritdoc}
     */
    public function printSetup(Formatter $formatter, Setup $setup)
    {
        if (!$setup instanceof HookedSetup) {
            return;
        }

        foreach ($setup->getHookCallResults() as $callResult) {
            $this->printSetupHookCallResult($formatter->getOutputPrinter(), $callResult);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function printTeardown(Formatter $formatter, Teardown $teardown)
    {
        if (!$teardown instanceof HookedTeardown) {
            return;
        }

        foreach ($teardown->getHookCallResults() as $callResult) {
            $this->printTeardownHookCallResult($formatter->getOutputPrinter(), $callResult);
        }
    }

    /**
     * Prints setup hook call result.
     *
     * @param OutputPrinter $printer
     * @param CallResult    $callResult
     */
    private function printSetupHookCallResult(OutputPrinter $printer, CallResult $callResult)
    {
        if (!$callResult->hasStdOut() && !$callResult->hasException()) {
            return;
        }

        $resultCode = $callResult->hasException() ? TestResult::FAILED : TestResult::PASSED;
        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $hook = $callResult->getCall()->getCallee();
        $path = $hook->getPath();

        $printer->writeln(
            sprintf('%s┌─ {+%s}@%s{-%s} {+comment}# %s{-comment}', $this->indentText, $style, $hook, $style, $path)
        );

        $printer->writeln(sprintf('%s│', $this->indentText));

        $this->printHookCallStdOut($printer, $callResult, $this->indentText);
        $this->printHookCallException($printer, $callResult, $this->indentText);

        if ($this->newlineBefore) {
            $printer->writeln();
        }
    }

    /**
     * Prints teardown hook call result.
     *
     * @param OutputPrinter $printer
     * @param CallResult    $callResult
     */
    private function printTeardownHookCallResult(OutputPrinter $printer, CallResult $callResult)
    {
        if (!$callResult->hasStdOut() && !$callResult->hasException()) {
            return;
        }

        $resultCode = $callResult->hasException() ? TestResult::FAILED : TestResult::PASSED;
        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $hook = $callResult->getCall()->getCallee();
        $path = $hook->getPath();

        $printer->writeln(sprintf('%s│', $this->indentText));

        $this->printHookCallStdOut($printer, $callResult, $this->indentText);
        $this->printHookCallException($printer, $callResult, $this->indentText);

        $printer->writeln(
            sprintf('%s└─ {+%s}@%s{-%s} {+comment}# %s{-comment}', $this->indentText, $style, $hook, $style, $path)
        );

        if ($this->newlineAfter) {
            $printer->writeln();
        }
    }

    /**
     * Prints hook call output (if has some).
     *
     * @param OutputPrinter $printer
     * @param CallResult    $callResult
     * @param string        $indentText
     */
    private function printHookCallStdOut(OutputPrinter $printer, CallResult $callResult, $indentText)
    {
        if (!$callResult->hasStdOut()) {
            return;
        }

        $pad = function ($line) use ($indentText) {
            return sprintf(
                '%s│  {+stdout}%s{-stdout}', $indentText, $line
            );
        };

        $printer->writeln(implode("\n", array_map($pad, explode("\n", $callResult->getStdOut()))));
        $printer->writeln(sprintf('%s│', $indentText));
    }

    /**
     * Prints hook call exception (if has some).
     *
     * @param OutputPrinter $printer
     * @param CallResult    $callResult
     * @param string        $indentText
     */
    private function printHookCallException(OutputPrinter $printer, CallResult $callResult, $indentText)
    {
        if (!$callResult->hasException()) {
            return;
        }

        $pad = function ($l) use ($indentText) {
            return sprintf(
                '%s╳  {+exception}%s{-exception}', $indentText, $l
            );
        };

        $exception = $this->exceptionPresenter->presentException($callResult->getException());
        $printer->writeln(implode("\n", array_map($pad, explode("\n", $exception))));
        $printer->writeln(sprintf('%s│', $indentText));
    }
}
