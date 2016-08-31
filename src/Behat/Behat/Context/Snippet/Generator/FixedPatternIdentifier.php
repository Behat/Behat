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
 * Identifier that always returns same pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FixedPatternIdentifier implements PatternIdentifier
{
    /**
     * @var string
     */
    private $patternType;

    /**
     * Initialises identifier.
     *
     * @param string $patternType
     */
    public function __construct($patternType)
    {
        $this->patternType = $patternType;
    }

    /**
     * {@inheritdoc}
     */
    public function guessPatternType($contextClass)
    {
        return $this->patternType;
    }
}
