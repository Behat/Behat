<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern;

/**
 * Step definition pattern.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Pattern
{
    public function __construct(
        private string $suggestedMethodName,
        private string $pattern,
        private int $placeholderCount = 0,
    ) {
    }

    /**
     * Returns canonical step text.
     *
     * @deprecated see getSuggestedMethodName
     */
    public function getCanonicalText(): string
    {
        return $this->suggestedMethodName;
    }

    public function getSuggestedMethodName(): string
    {
        return $this->suggestedMethodName;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getPlaceholderCount(): int
    {
        return $this->placeholderCount;
    }
}
