<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\Context;

/**
 * Identifies target pattern for snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface PatternIdentifier
{
    /**
     * Attempts to guess the target pattern type from the context.
     *
     * @param string $contextClass
     *
     * @return null|string
     */
    public function guessPatternType($contextClass);
}
