<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\Printer;

use Behat\Behat\Context\Snippet\Generator\CannotGenerateStepPatternException;
use Behat\Behat\Definition\Translator\TranslatorInterface;
use Behat\Behat\Snippet\AggregateSnippet;
use Behat\Gherkin\Node\StepNode;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

use function count;

/**
 * Behat console-based snippet printer.
 *
 * Extends default printer with default styles.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConsoleSnippetPrinter implements SnippetPrinter
{
    /**
     * Initializes printer.
     */
    public function __construct(
        private readonly OutputInterface $output,
        private readonly TranslatorInterface $translator,
    ) {
        $this->output->getFormatter()->setStyle('snippet_keyword', new OutputFormatterStyle(null, null, ['bold']));
        $this->output->getFormatter()->setStyle('snippet_undefined', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('snippet_failure', new OutputFormatterStyle('red'));
    }

    /**
     * Prints snippets of specific target.
     *
     * @param string             $targetName
     * @param AggregateSnippet[] $snippets
     */
    public function printSnippets($targetName, array $snippets)
    {
        $message = $this->translator->trans('snippet_proposal_title', ['%count%' => $targetName], 'output');

        $this->output->writeln('--- ' . $message . PHP_EOL);

        $usedClasses = [];
        foreach ($snippets as $snippet) {
            foreach ($snippet->getUsedClasses() as $usedClass) {
                $usedClasses[$usedClass] = true;
            }

            $this->output->writeln(sprintf('<snippet_undefined>%s</snippet_undefined>', $snippet->getSnippet()) . PHP_EOL);
        }

        $this->outputClassesUsesStatements(array_keys($usedClasses));
    }

    /**
     * Prints undefined steps of specific suite.
     *
     * @param string     $suiteName
     * @param StepNode[] $steps
     */
    public function printUndefinedSteps($suiteName, array $steps)
    {
        $message = $this->translator->trans('snippet_missing_title', ['%count%' => $suiteName], 'output');

        $this->output->writeln('--- ' . $message . PHP_EOL);

        foreach ($steps as $step) {
            $this->output->writeln(sprintf('    <snippet_undefined>%s %s</snippet_undefined>', $step->getKeyword(), $step->getText()));
        }

        $this->output->writeln('');
    }

    /**
     * @param array<string> $usedClasses
     */
    public function outputClassesUsesStatements(array $usedClasses): void
    {
        if ([] === $usedClasses) {
            return;
        }

        $message = $this->translator->trans('snippet_proposal_use', ['%count%' => count($usedClasses)], 'output');

        $this->output->writeln('--- ' . $message . PHP_EOL);

        foreach ($usedClasses as $usedClass) {
            $this->output->writeln(sprintf('    <snippet_undefined>use %s;</snippet_undefined>', $usedClass));
        }
    }

    /**
     * @param array<CannotGenerateStepPatternException> $exceptions
     */
    public function printSnippetGenerationFailures(array $exceptions): void
    {
        if ([] === $exceptions) {
            return;
        }

        $title = $this->translator->trans('snippet_generation_failure_title', [], 'output');
        $hint = $this->translator->trans('snippet_generation_failure_hint', [], 'output');

        $this->output->writeln('<snippet_failure>--- '.$title.'</snippet_failure>');
        $this->output->writeln('<snippet_failure>    '.$hint.'</snippet_failure>');
        $this->output->writeln('');

        foreach ($exceptions as $exception) {
            $this->output->writeln('<snippet_failure>    - '.$exception->stepText.'</snippet_failure>');
        }
    }
}
