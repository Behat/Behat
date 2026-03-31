<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer;

use Behat\Behat\Definition\Translator\TranslatorInterface;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Statistics\HookStat;
use Behat\Behat\Output\Statistics\ScenarioStat;
use Behat\Behat\Output\Statistics\StepStatV2;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Hook\Scope\HookScope;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat list printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ListPrinter
{
    private readonly ConfigurablePathPrinter $configurablePathPrinter;

    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly TranslatorInterface $translator,
        string $basePath,
        ?ConfigurablePathPrinter $configurablePathPrinter = null,
    ) {
        $this->configurablePathPrinter = $configurablePathPrinter ?? new ConfigurablePathPrinter($basePath, printAbsolutePaths: false, editorUrl: null);
    }

    /**
     * Prints scenarios list.
     *
     * @param string         $intro
     * @param int            $resultCode
     * @param ScenarioStat[] $scenarioStats
     * @param StepStatV2[]     $stepStats
     */
    public function printScenariosList(OutputPrinter $printer, $intro, $resultCode, array $scenarioStats, ?array $stepStats = null): void
    {
        if (!count($scenarioStats)) {
            return;
        }

        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $intro = $this->translator->trans($intro, [], 'output');

        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($scenarioStats as $stat) {
            $path = $this->configurablePathPrinter->processPathsInText((string) $stat);

            $path = $this->appendFailingStepText($stepStats, $path, $stat);

            $printer->writeln(sprintf('    {+%s}%s{-%s}', $style, $path, $style));
        }

        $printer->writeln();
    }

    /**
     * Prints step list.
     *
     * @param string     $intro
     * @param int        $resultCode
     * @param StepStatV2[] $stepStats
     */
    public function printStepList(
        OutputPrinter $printer,
        $intro,
        $resultCode,
        array $stepStats,
        ?ShowOutputOption $showOutput = ShowOutputOption::InSummary,
    ): void {
        if (!count($stepStats)) {
            return;
        }

        $style = $this->resultConverter->convertResultCodeToString($resultCode);
        $intro = $this->translator->trans($intro, [], 'output');

        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));

        foreach ($stepStats as $num => $stepStat) {
            $this->printStepStat($printer, $num + 1, $stepStat, $style, $showOutput);
        }
    }

    /**
     * Prints failed hooks list.
     *
     * @param HookStat[]    $failedHookStats
     */
    public function printFailedHooksList(
        OutputPrinter $printer,
        string $intro,
        array $failedHookStats,
        bool $simple = false,
    ): void {
        if (!count($failedHookStats)) {
            return;
        }

        $style = $this->resultConverter->convertResultCodeToString(TestResult::FAILED);
        $intro = $this->translator->trans($intro, [], 'output');

        $printer->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $intro, $style));
        foreach ($failedHookStats as $hookStat) {
            $this->printHookStat($printer, $hookStat, $style, $simple);
        }
        if ($simple) {
            $printer->writeln();
        }
    }

    /**
     * Prints hook stat.
     */
    private function printHookStat(OutputPrinter $printer, HookStat $hookStat, string $style, bool $simple): void
    {
        $location = $this->getLocationFromScope($hookStat->getScope());
        $printer->writeln(
            sprintf(
                '    {+%s}%s{-%s}%s {+comment}# %s{-comment}',
                $style,
                $hookStat->getName(),
                $style,
                $location ? " \"$location\"" : '',
                $this->configurablePathPrinter->processPathsInText($hookStat->getPath())
            )
        );

        if ($simple) {
            return;
        }
        $pad = (fn ($line): string => '      ' . $line);

        if (null !== $hookStat->getStdOut()) {
            $padText = (fn ($line): string => '      │ ' . $line);
            $stdOutString = array_map($padText, explode("\n", $hookStat->getStdOut()));
            $printer->writeln(implode("\n", $stdOutString));
        }

        if ($hookStat->getError()) {
            $exceptionString = implode("\n", array_map($pad, explode("\n", $hookStat->getError())));
            $printer->writeln(sprintf('{+%s}%s{-%s}', $style, $exceptionString, $style));
        }

        $printer->writeln();
    }

    private function printStepStat(
        OutputPrinter $printer,
        int $number,
        StepStatV2 $stat,
        string $style,
        ?ShowOutputOption $showOutput,
    ): void {
        $maxLength = max(mb_strlen($stat->getScenarioText(), 'utf8'), mb_strlen($stat->getStepText(), 'utf8') + 2) + 1;

        $printer->writeln(
            sprintf(
                '%03d {+%s}%s{-%s}%s{+comment}# %s{-comment}',
                $number,
                $style,
                $stat->getScenarioText(),
                $style,
                str_pad(' ', $maxLength - mb_strlen($stat->getScenarioText(), 'utf8')),
                $this->configurablePathPrinter->processPathsInText($stat->getScenarioPath())
            )
        );

        $printer->writeln(
            sprintf(
                '      {+%s}%s{-%s}%s{+comment}# %s{-comment}',
                $style,
                $stat->getStepText(),
                $style,
                str_pad(' ', $maxLength - mb_strlen($stat->getStepText(), 'utf8') - 2),
                $this->configurablePathPrinter->processPathsInText($stat->getStepPath())
            )
        );

        $pad = (fn ($line): string => '        ' . $line);

        if (null !== $stat->getStdOut() && $showOutput !== ShowOutputOption::No) {
            $padText = (fn ($line): string => '        │ ' . $line);
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
     * @param StepStatV2[] $stepStats
     */
    private function appendFailingStepText(?array $stepStats, string $path, ScenarioStat $scenarioStat): string
    {
        if ($stepStats === null) {
            return $path;
        }

        $foundStepStat = null;
        foreach ($stepStats as $stepStat) {
            if ($stepStat->getScenarioPath() === $scenarioStat->getPath()) {
                $foundStepStat = $stepStat;
                break;
            }
        }

        if ($foundStepStat === null) {
            return $path;
        }

        $stepLine = $this->extractLineNumber($foundStepStat->getStepPath());

        if ($stepLine !== null) {
            $lineNumber = $this->translator->trans('on_line_number', ['%line%' => $stepLine], 'output');
            $lineHelper = ' (' . $lineNumber . ')';
        } else {
            $lineHelper = '';
        }

        return $path . $lineHelper;
    }

    private function extractLineNumber(string $path): ?string
    {
        $lastColonPos = strrpos($path, ':');

        if (false === $lastColonPos) {
            return null;
        }

        return substr($path, $lastColonPos + 1);
    }

    private function getLocationFromScope(?HookScope $scope): ?string
    {
        if ($scope instanceof HookScope) {
            return match (true) {
                $scope instanceof BeforeSuiteScope,
                $scope instanceof AfterSuiteScope => $scope->getSuite()->getName(),
                $scope instanceof BeforeFeatureScope,
                $scope instanceof AfterFeatureScope => $this->configurablePathPrinter->processPathsInText(
                    $scope->getFeature()->getFile()
                ),
                $scope instanceof BeforeScenarioScope,
                $scope instanceof AfterScenarioScope => $this->configurablePathPrinter->processPathsInText(
                    $scope->getFeature()->getFile() . ':' . $scope->getScenario()->getLine()
                ),
                $scope instanceof BeforeStepScope,
                $scope instanceof AfterStepScope => $this->configurablePathPrinter->processPathsInText(
                    $scope->getFeature()->getFile() . ':' . $scope->getStep()->getLine()
                ),
                default => null,
            };
        }

        return null;
    }
}
