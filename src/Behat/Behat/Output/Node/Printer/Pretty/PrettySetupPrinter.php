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
use Behat\Testwork\Hook\Call\RuntimeHook;
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
     * @var string
     */
    private $indentText;

    /**
     * Initializes printer.
     *
     * @param int  $indentation
     * @param bool $newlineBefore
     * @param bool $newlineAfter
     */
    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly ExceptionPresenter $exceptionPresenter,
        $indentation = 0,
        private $newlineBefore = false,
        private $newlineAfter = false,
    ) {
        $this->indentText = str_repeat(' ', intval($indentation));
    }

    public function printSetup(Formatter $formatter, Setup $setup): void
    {
        if (!$setup instanceof HookedSetup) {
            return;
        }

        foreach ($setup->getHookCallResults() as $callResult) {
            $this->printSetupHookCallResult($formatter->getOutputPrinter(), $callResult);
        }
    }

    public function printTeardown(Formatter $formatter, Teardown $teardown): void
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
     */
    private function printSetupHookCallResult(OutputPrinter $printer, CallResult $callResult): void
    {
        if (!$callResult->hasStdOut() && !$callResult->hasException()) {
            return;
        }

        $resultCode = $callResult->hasException() ? TestResult::FAILED : TestResult::PASSED;
        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $hook = $callResult->getCall()->getCallee();
        $path = $hook->getPath();

        assert($hook instanceof RuntimeHook);
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
     */
    private function printTeardownHookCallResult(OutputPrinter $printer, CallResult $callResult): void
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

        assert($hook instanceof RuntimeHook);
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
     * @param string        $indentText
     */
    private function printHookCallStdOut(OutputPrinter $printer, CallResult $callResult, $indentText): void
    {
        if (!$callResult->hasStdOut()) {
            return;
        }

        $pad = (fn ($line): string => sprintf(
            '%s│  {+stdout}%s{-stdout}',
            $indentText,
            $line
        ));

        $printer->writeln(implode("\n", array_map($pad, explode("\n", (string) $callResult->getStdOut()))));
        $printer->writeln(sprintf('%s│', $indentText));
    }

    /**
     * Prints hook call exception (if has some).
     *
     * @param string        $indentText
     */
    private function printHookCallException(OutputPrinter $printer, CallResult $callResult, $indentText): void
    {
        if (!$callResult->hasException()) {
            return;
        }

        $pad = (fn ($l): string => sprintf(
            '%s╳  {+exception}%s{-exception}',
            $indentText,
            $l
        ));

        $exception = $this->exceptionPresenter->presentException($callResult->getException());
        $printer->writeln(implode("\n", array_map($pad, explode("\n", $exception))));
        $printer->writeln(sprintf('%s│', $indentText));
    }
}
