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
use Behat\Behat\Output\Statistics\HookStat;
use Behat\Behat\Output\Statistics\ScenarioStat;
use Behat\Behat\Output\Statistics\StepStatV2;
use Behat\Behat\Output\Statistics\StepStat;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;
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
     * @param OutputPrinter  $printer
     * @param string         $intro
     * @param integer        $resultCode
     * @param ScenarioStat[] $scenarioStats
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
            $path = $this->relativizePaths((string) $stat);
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

        foreach ($stepStats as $num => $stepStat) {
            if ($stepStat instanceof StepStatV2) {
                $this->printStepStat($printer, $num + 1, $stepStat, $style);
            } elseif ($stepStat instanceof StepStat) {
                $this->printStat($printer, $stepStat->getText(), $stepStat->getPath(), $style, $stepStat->getStdOut(), $stepStat->getError());
            }
        }
    }

    /**
     * Prints failed hooks list.
     *
     * @param OutputPrinter $printer
     * @param string        $intro
     * @param HookStat[]    $failedHookStats
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
            $this->printHookStat($printer, $hookStat, $style);
        }
    }

    /**
     * Prints hook stat.
     *
     * @param OutputPrinter $printer
     * @param string        $name
     * @param string        $path
     * @param string        $style
     * @param null|string   $stdOut
     * @param null|string   $error
     *
     * @deprecated Remove in 4.0
     */
    private function printStat(OutputPrinter $printer, $name, $path, $style, $stdOut, $error)
    {
        $path = $this->relativizePaths($path);
        $printer->writeln(sprintf('    {+%s}%s{-%s} {+comment}# %s{-comment}', $style, $name, $style, $path));

        $pad = function ($line) { return '      ' . $line; };

        if (null !== $stdOut) {
            $padText = function ($line) { return '      │ ' . $line; };
            $stdOutString = array_map($padText, explode("\n", $stdOut));
            $printer->writeln(implode("\n", $stdOutString));
        }

        if ($error) {
            $exceptionString = implode("\n", array_map($pad, explode("\n", $error)));
            $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $exceptionString, $style));
        }

        $printer->writeln();
    }

    /**
     * Prints hook stat.
     *
     * @param OutputPrinter $printer
     * @param HookStat      $hookStat
     * @param string        $style
     */
    private function printHookStat(OutputPrinter $printer, HookStat $hookStat, $style)
    {
        $printer->writeln(
            sprintf('    {+%s}%s{-%s} {+comment}# %s{-comment}',
                $style, $hookStat->getName(), $style, $this->relativizePaths($hookStat->getPath())
            )
        );

        $pad = function ($line) { return '      ' . $line; };

        if (null !== $hookStat->getStdOut()) {
            $padText = function ($line) { return '      │ ' . $line; };
            $stdOutString = array_map($padText, explode("\n", $hookStat->getStdOut()));
            $printer->writeln(implode("\n", $stdOutString));
        }

        if ($hookStat->getError()) {
            $exceptionString = implode("\n", array_map($pad, explode("\n", $hookStat->getError())));
            $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $exceptionString, $style));
        }

        $printer->writeln();
    }

    /**
     * Prints hook stat.
     *
     * @param OutputPrinter $printer
     * @param integer       $number
     * @param StepStatV2    $stat
     * @param string        $style
     */
    private function printStepStat(OutputPrinter $printer, $number, StepStatV2 $stat, $style)
    {
        $maxLength = max(mb_strlen($stat->getScenarioText(), 'utf8'), mb_strlen($stat->getStepText(), 'utf8') + 2) + 1;

        $printer->writeln(
            sprintf('%03d {+%s}%s{-%s}%s{+comment}# %s{-comment}',
                $number,
                $style,
                $stat->getScenarioText(),
                $style,
                str_pad(' ', $maxLength - mb_strlen($stat->getScenarioText(), 'utf8')),
                $this->relativizePaths($stat->getScenarioPath())
            )
        );

        $printer->writeln(
            sprintf('      {+%s}%s{-%s}%s{+comment}# %s{-comment}',
                $style,
                $stat->getStepText(),
                $style,
                str_pad(' ', $maxLength - mb_strlen($stat->getStepText(), 'utf8') - 2),
                $this->relativizePaths($stat->getStepPath())
            )
        );

        $pad = function ($line) { return '        ' . $line; };

        if (null !== $stat->getStdOut()) {
            $padText = function ($line) { return '        │ ' . $line; };
            $stdOutString = array_map($padText, explode("\n", $stat->getStdOut()));
            $printer->writeln(implode("\n", $stdOutString));
        }

        if ($stat->getError()) {
            $exceptionString = implode("\n", array_map($pad, explode("\n", $stat->getError())));
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
