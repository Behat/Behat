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
        private string $canonicalText,
        private string $pattern,
        private int $placeholderCount = 0,
    ) {
    }

    /**
     * Returns canonical step text.
     *
     * @return string
     */
    public function getCanonicalText()
    {
        return $this->canonicalText;
    }

    /**
     * Returns pattern.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Returns pattern placeholder count.
     *
     * @return int
     */
    public function getPlaceholderCount()
    {
        return $this->placeholderCount;
    }
}
