<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Behat\Snippet\Appender\SnippetAppender;
use Behat\Behat\Snippet\Printer\SnippetPrinter;

/**
 * Prints or appends snippets to a specific environment using registered appenders and printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SnippetWriter
{
    /**
     * @var SnippetAppender[]
     */
    private $appenders = array();

    /**
     * Registers snippet appender.
     *
     * @param SnippetAppender $appender
     */
    public function registerSnippetAppender(SnippetAppender $appender)
    {
        $this->appenders[] = $appender;
    }

    /**
     * Appends snippets to appropriate targets.
     *
     * @param AggregateSnippet[] $snippets
     */
    public function appendSnippets(array $snippets)
    {
        foreach ($snippets as $snippet) {
            $this->appendSnippet($snippet);
        }
    }

    /**
     * Prints snippets using provided printer.
     *
     * @param SnippetPrinter     $printer
     * @param AggregateSnippet[] $snippets
     */
    public function printSnippets(SnippetPrinter $printer, array $snippets)
    {
        $printableSnippets = array();
        foreach ($snippets as $snippet) {
            foreach ($snippet->getTargets() as $target) {
                $targetSnippets = array();

                if (isset($printableSnippets[$target])) {
                    $targetSnippets = $printableSnippets[$target];
                }

                $targetSnippets[] = $snippet;
                $printableSnippets[$target] = $targetSnippets;
            }
        }

        foreach ($printableSnippets as $target => $targetSnippets) {
            $printer->printSnippets($target, $targetSnippets);
        }
    }

    /**
     * Prints undefined steps using provided printer.
     *
     * @param SnippetPrinter  $printer
     * @param UndefinedStep[] $undefinedSteps
     */
    public function printUndefinedSteps(SnippetPrinter $printer, array $undefinedSteps)
    {
        $printableSteps = array();
        foreach ($undefinedSteps as $undefinedStep) {
            $suiteName = $undefinedStep->getEnvironment()->getSuite()->getName();
            $step = $undefinedStep->getStep();

            if (!isset($printableSteps[$suiteName])) {
                $printableSteps[$suiteName] = array();
            }

            $printableSteps[$suiteName][$step->getText()] = $step;
        }

        foreach ($printableSteps as $suiteName => $steps) {
            $printer->printUndefinedSteps($suiteName, array_values($steps));
        }
    }

    /**
     * Appends snippet to appropriate targets.
     *
     * @param AggregateSnippet $snippet
     */
    private function appendSnippet(AggregateSnippet $snippet)
    {
        foreach ($this->appenders as $appender) {
            if (!$appender->supportsSnippet($snippet)) {
                continue;
            }

            $appender->appendSnippet($snippet);
        }
    }
}
