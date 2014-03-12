<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern\Policy;

use Behat\Behat\Definition\Pattern\Pattern;
use Behat\Behat\Definition\Pattern\PatternTransformer;

/**
 * Defines a way to handle custom definition patterns.
 *
 * @see PatternTransformer
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface PatternPolicy
{
    /**
     * Checks if policy supports pattern type.
     *
     * @param string $type
     *
     * @return Boolean
     */
    public function supportsPatternType($type);

    /**
     * Generates pattern for step text.
     *
     * @param string $stepText
     *
     * @return Pattern
     */
    public function generatePattern($stepText);

    /**
     * Checks if policy supports pattern.
     *
     * @param string $pattern
     *
     * @return Boolean
     */
    public function supportsPattern($pattern);

    /**
     * Transforms pattern string to regex.
     *
     * @param string $pattern
     *
     * @return string
     */
    public function transformPatternToRegex($pattern);
}
