<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

/**
 * Provides snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SnippetRepository
{
    /**
     * Returns all generated snippets.
     *
     * @return AggregateSnippet[]
     */
    public function getSnippets();

    /**
     * Returns steps for which there was no snippet generated.
     *
     * @return UndefinedStep[]
     */
    public function getUndefinedSteps();
}
