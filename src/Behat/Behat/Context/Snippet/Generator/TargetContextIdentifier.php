<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\Environment\ContextEnvironment;

/**
 * Identifies target context for snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface TargetContextIdentifier
{
    /**
     * Attempts to guess the target context class from the environment.
     *
     * @param ContextEnvironment $environment
     *
     * @return null|string
     */
    public function guessTargetContextClass(ContextEnvironment $environment);
}
