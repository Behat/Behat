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
    public function __construct(
        private readonly ?string $patternType = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function guessPatternType($contextClass): ?string
    {
        return $this->patternType;
    }
}
