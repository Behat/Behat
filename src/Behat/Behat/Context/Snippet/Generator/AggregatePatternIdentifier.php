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
 * Uses multiple child identifiers - the first one that returns non-null result would
 * be the winner.
 */
final class AggregatePatternIdentifier implements PatternIdentifier
{
    /**
     * @var PatternIdentifier[]
     */
    private $identifiers;

    /**
     * Initialises identifier.
     *
     * @param PatternIdentifier[] $identifiers
     */
    public function __construct(array $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function guessPatternType($contextClass)
    {
        foreach ($this->identifiers as $identifier) {
            $pattern = $identifier->guessPatternType($contextClass);

            if (null !== $pattern) {
                return $pattern;
            }
        }

        return null;
    }
}
