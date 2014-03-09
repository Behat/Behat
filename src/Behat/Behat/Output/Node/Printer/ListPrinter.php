<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Statistics\FailedHookStat;
use Behat\Behat\Output\Statistics\StepStat;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Behat list printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ListPrinter
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
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var string
     */
    private $basePath;

    /**
     * Initializes printer.
     *
     * @param ResultToStringConverter $resultConverter
     * @param ExceptionPresenter      $exceptionPresenter
     * @param TranslatorInterface     $translator
     * @param string                  $basePath
     */
    public function __construct(
        ResultToStringConverter $resultConverter,
        ExceptionPresenter $exceptionPresenter,
        TranslatorInterface $translator,
        $basePath
    ) {
        $this->resultConverter = $resultConverter;
        $this->exceptionPresenter = $exceptionPresenter;
        $this->translator = $translator;
        $this->basePath = $basePath;
    }

    /**
     * Prints scenarios list.
     *
     * @param OutputPrinter $printer
     * @param string        $intro
     * @param integer       $resultCode
     * @param array         $scenarioStats
     */
    public function printScenariosList(OutputPrinter $printer, $intro, $resultCode, array $scenarioStats)
    {
        if (!count($scenarioStats)) {
            return;
        }

        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $intro = $this->translator->trans($intro, array(), 'output');

        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($scenarioStats as $stat) {
            $path = $this->relativizePaths((string)$stat);
            $printer->writeln(sprintf('    {+%s}%s{-%s}', $style, $path, $style));
        }

        $printer->writeln();
    }

    /**
     * Prints step list.
     *
     * @param OutputPrinter $printer
     * @param string        $intro
     * @param integer       $resultCode
     * @param StepStat[]    $stepStats
     */
    public function printStepList(OutputPrinter $printer, $intro, $resultCode, array $stepStats)
    {
        if (!count($stepStats)) {
            return;
        }

        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $intro = $this->translator->trans($intro, array(), 'output');

        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));

        foreach ($stepStats as $stepStat) {
            $name = $stepStat->getText();
            $path = $stepStat->getPath();
            $stdOut = $stepStat->getStdOut();
            $exception = $stepStat->getException();

            $this->printHookStat($printer, $name, $path, $style, $stdOut, $exception);
        }
    }

    /**
     * Prints failed hooks list.
     *
     * @param OutputPrinter    $printer
     * @param string           $intro
     * @param FailedHookStat[] $failedHookStats
     */
    public function printFailedHooksList(OutputPrinter $printer, $intro, array $failedHookStats)
    {
        if (!count($failedHookStats)) {
            return;
        }

        $style = $this->resultConverter->convertResultCodeToString(TestResult::FAILED);
        $intro = $this->translator->trans($intro, array(), 'output');

        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($failedHookStats as $hookStat) {
            $name = $hookStat->getName();
            $path = $hookStat->getPath();
            $stdOut = $hookStat->getStdOut();
            $exception = $hookStat->getException();

            $this->printHookStat($printer, $name, $path, $style, $stdOut, $exception);
        }
    }

    /**
     * Prints hook stat.
     *
     * @param OutputPrinter  $printer
     * @param string         $name
     * @param string         $path
     * @param string         $style
     * @param null|string    $stdOut
     * @param null|Exception $exception
     */
    private function printHookStat(OutputPrinter $printer, $name, $path, $style, $stdOut, Exception $exception)
    {
        $printer->writeln(sprintf('    {+%s}%s{-%s} {+comment}# %s{-comment}', $style, $name, $style, $path));

        $pad = function ($line) { return '      ' . $line; };

        if (null !== $stdOut) {
            $padText = function ($line) { return '      │ ' . $line; };
            $stdOutString = array_map($padText, explode("\n", $stdOut));
            $printer->writeln(implode("\n", $stdOutString));
        }

        if ($exception) {
            if ($exception instanceof PendingException) {
                $exception = $exception->getMessage();
            } else {
                $exception = $this->exceptionPresenter->presentException($exception);
            }

            $exceptionString = implode("\n", array_map($pad, explode("\n", $exception)));
            $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $exceptionString, $style));
        }

        $printer->writeln();
    }

    /**
     * Transforms path to relative.
     *
     * @param string $path
     *
     * @return string
     */
    private function relativizePaths($path)
    {
        if (!$this->basePath) {
            return $path;
        }

        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}
