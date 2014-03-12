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
    /**
     * @var string
     */
    private $canonicalText;
    /**
     * @var string
     */
    private $pattern;
    /**
     * @var integer
     */
    private $placeholderCount;

    /**
     * Initializes pattern.
     *
     * @param string  $canonicalText
     * @param string  $pattern
     * @param integer $placeholderCount
     */
    public function __construct($canonicalText, $pattern, $placeholderCount = 0)
    {
        $this->canonicalText = $canonicalText;
        $this->pattern = $pattern;
        $this->placeholderCount = $placeholderCount;
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
     * @return integer
     */
    public function getPlaceholderCount()
    {
        return $this->placeholderCount;
    }
}
