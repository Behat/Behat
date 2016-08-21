<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context;

use Behat\Behat\Context\Snippet\Generator\ContextSnippetGenerator;

/**
 * Context that implements this interface is treated as a snippet-friendly context.
 *
 * @see ContextSnippetGenerator
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated will be removed in 4.0. Use --snippets-for CLI option instead
 */
interface SnippetAcceptingContext extends Context
{
}
