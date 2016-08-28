<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

/**
 * Identifier that uses context interfaces to guess the pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of --snippet-type and will be removed in 4.0
 */
final class ContextInterfaceBasedPatternIdentifier implements PatternIdentifier
{
    /**
     * {@inheritdoc}
     */
    public function guessPatternType($contextClass)
    {
        if (!in_array('Behat\Behat\Context\CustomSnippetAcceptingContext', class_implements($contextClass))) {
            return null;
        }

        return $contextClass::getAcceptedSnippetType();
    }
}
