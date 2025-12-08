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
use Behat\Behat\Output\Statistics\StepStat;
use Behat\Behat\Output\Statistics\StepStatV2;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Testwork\Exception\ExceptionPresenter;
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

    /**
     * @param ExceptionPresenter $exceptionPresenter deprecated , will be removed in the next major version
     */
    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        ExceptionPresenter $exceptionPresenter,
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
     * @param StepStat[]     $stepStats
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
     * @param StepStat[] $stepStats
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
            if ($stepStat instanceof StepStatV2) {
                $this->printStepStat($printer, $num + 1, $stepStat, $style, $showOutput);
            } elseif ($stepStat instanceof StepStat) {
                $this->printStat(
                    $printer,
                    $stepStat->getText(),
                    $stepStat->getPath(),
                    $style,
                    $stepStat->getStdOut(),
                    $stepStat->getError(),
                    $showOutput
                );
            }
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
     *
     * @deprecated Remove in 4.0
     */
    private function printStat(
        OutputPrinter $printer,
        string $name,
        string $path,
        string $style,
        ?string $stdOut,
        ?string $error,
        ?ShowOutputOption $showOutput,
    ): void {
        $path = $this->configurablePathPrinter->processPathsInText($path);
        $printer->writeln(sprintf('    {+%s}%s{-%s} {+comment}# %s{-comment}', $style, $name, $style, $path));

        $pad = (fn ($line): string => '      ' . $line);

        if (null !== $stdOut && $showOutput !== ShowOutputOption::No) {
            $padText = (fn ($line): string => '      │ ' . $line);
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
     * @param StepStat[] $stepStats
     */
    private function appendFailingStepText(?array $stepStats, string $path, ScenarioStat $scenarioStat): string
    {
        $foundStepStat = null;
        if (null === $stepStats) {
            return $path;
        }

        foreach ($stepStats as $stepStat) {
            // See https://github.com/Behat/Behat/pull/1615#pullrequestreview-2737706542
            // > although Statistics::getFailedSteps() is typed as returning StepStat[] for BC reasons,
            // > in practice we only ever create / register instances of StepStatV2. And that has a
            // > getScenarioPath() which I think should always match the getPath() of ScenarioStat.
            if ($stepStat instanceof StepStatV2 && $stepStat->getScenarioPath() === $scenarioStat->getPath()) {
                $foundStepStat = $stepStat;
                break;
            }
        }

        if (!$foundStepStat instanceof StepStatV2) {
            return $path;
        }

        $stepLine = $this->extractLineNumber((string) $foundStepStat->getStepPath());

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
