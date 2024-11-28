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
 * Identifier that always returns same pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FixedPatternIdentifier implements PatternIdentifier
{
    private ?string $patternType;

    /**
     * Initialises identifier.
     */
    public function __construct(?string $patternType = null)
    {
        $this->patternType = $patternType;
    }

    /**
     * {@inheritdoc}
     */
    public function guessPatternType($contextClass): ?string
    {
        return $this->patternType;
    }
}
