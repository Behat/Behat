<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\Printer;

use Behat\Behat\Snippet\AggregateSnippet;
use Behat\Gherkin\Node\StepNode;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var OutputInterface
     */
    private $output;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes printer.
     *
     * @param OutputInterface     $output
     * @param TranslatorInterface $translator
     */
    public function __construct(OutputInterface $output, TranslatorInterface $translator)
    {
        $this->output = $output;
        $this->translator = $translator;

        $output->getFormatter()->setStyle('snippet_keyword', new OutputFormatterStyle(null, null, array('bold')));
        $output->getFormatter()->setStyle('snippet_undefined', new OutputFormatterStyle('yellow'));
    }

    /**
     * Prints snippets of specific target.
     *
     * @param string             $targetName
     * @param AggregateSnippet[] $snippets
     */
    public function printSnippets($targetName, array $snippets)
    {
        $message = $this->translator->trans('snippet_proposal_title', array('%1%' => $targetName), 'output');

        $this->output->writeln('--- ' . $message . PHP_EOL);

        foreach ($snippets as $snippet) {
            $this->output->writeln(sprintf('<snippet_undefined>%s</snippet_undefined>', $snippet->getSnippet()) . PHP_EOL);
        }
    }

    /**
     * Prints undefined steps of specific suite.
     *
     * @param string     $suiteName
     * @param StepNode[] $steps
     */
    public function printUndefinedSteps($suiteName, array $steps)
    {
        $message = $this->translator->trans('snippet_missing_title', array('%1%' => $suiteName), 'output');

        $this->output->writeln('--- ' . $message . PHP_EOL);

        foreach ($steps as $step) {
            $this->output->writeln(sprintf('    <snippet_undefined>%s %s</snippet_undefined>', $step->getKeyword(), $step->getText()));
        }

        $this->output->writeln('');
    }
}
