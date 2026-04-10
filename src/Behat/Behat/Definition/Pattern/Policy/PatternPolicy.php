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
 *
 * @api
 */
interface PatternPolicy
{
    /**
     * Checks if policy supports pattern type.
     */
    public function supportsPatternType(?string $type): bool;

    /**
     * Generates pattern for step text.
     */
    public function generatePattern(string $stepText): Pattern;

    /**
     * Checks if policy supports pattern.
     */
    public function supportsPattern(string $pattern): bool;

    /**
     * Transforms pattern string to regex.
     */
    public function transformPatternToRegex(string $pattern): string;
}
