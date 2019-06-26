<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\Appender;

use Behat\Behat\Snippet\AggregateSnippet;
use Behat\Behat\Snippet\SnippetWriter;

/**
 * Appends snippets to its targets. Used by SnippetWriter.
 *
 * @see SnippetWriter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SnippetAppender
{
    /**
     * Checks if appender supports snippet.
     *
     * @param AggregateSnippet $snippet
     *
     * @return bool
     */
    public function supportsSnippet(AggregateSnippet $snippet);

    /**
     * Appends snippet to the source.
     *
     * @param AggregateSnippet $snippet
     */
    public function appendSnippet(AggregateSnippet $snippet);
}
