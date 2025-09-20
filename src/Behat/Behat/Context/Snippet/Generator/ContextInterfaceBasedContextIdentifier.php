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
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Identifier that uses context interfaces to guess which one is target.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of --snippets-for and will be removed in 4.0
 */
final class ContextInterfaceBasedContextIdentifier implements TargetContextIdentifier
{
    public function guessTargetContextClass(ContextEnvironment $environment)
    {
        foreach ($environment->getContextClasses() as $class) {
            if (in_array(SnippetAcceptingContext::class, class_implements($class))) {
                return $class;
            }
        }

        return null;
    }
}
