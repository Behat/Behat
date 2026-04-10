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

/**
 * Prints all snippets for a target.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SnippetPrinter
{
    /**
     * Prints snippets of the specific target.
     *
     * @param AggregateSnippet[] $snippets
     */
    public function printSnippets(string $targetName, array $snippets): void;

    /**
     * Prints undefined steps of the specific suite.
     *
     * @param StepNode[] $steps
     */
    public function printUndefinedSteps(string $suiteName, array $steps): void;
}
