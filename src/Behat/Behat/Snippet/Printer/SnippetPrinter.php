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

/**
 * Snippet printer interface.
 *
 * Prints all snippets for a target.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SnippetPrinter
{
    /**
     * Prints snippets of specific target.
     *
     * @param string             $target
     * @param AggregateSnippet[] $snippets
     */
    public function printSnippets($target, array $snippets);
}
