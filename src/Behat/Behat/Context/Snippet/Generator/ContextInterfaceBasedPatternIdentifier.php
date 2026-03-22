<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\CustomSnippetAcceptingContext;
use Behat\Testwork\Deprecation\DeprecationCollector;

/**
 * Identifier that uses context interfaces to guess the pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of --snippet-type and will be removed in 4.0
 */
final class ContextInterfaceBasedPatternIdentifier implements PatternIdentifier
{
    public function guessPatternType($contextClass)
    {
        if (!in_array(CustomSnippetAcceptingContext::class, class_implements($contextClass))) {
            return null;
        }

        DeprecationCollector::trigger('ContextInterfaceBasedPatternIdentifier is deprecated in favour of --snippet-type and will be removed in 4.0.');

        return $contextClass::getAcceptedSnippetType();
    }
}
