<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern;

use Behat\Behat\Definition\Exception\UnknownPatternException;
use Behat\Behat\Definition\Exception\UnsupportedPatternTypeException;
use Behat\Behat\Definition\Pattern\Policy\PatternPolicy;

/**
 * Transforms patterns using registered policies.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PatternTransformer
{
    /**
     * @var PatternPolicy[]
     */
    private $policies = array();

    /**
     * @var string[]
     */
    private $patternToRegexpCache = array();

    /**
     * Registers pattern policy.
     *
     * @param PatternPolicy $policy
     */
    public function registerPatternPolicy(PatternPolicy $policy)
    {
        $this->policies[] = $policy;
        $this->patternToRegexpCache = array();
    }

    /**
     * Generates pattern.
     *
     * @param string $type
     * @param string $stepText
     *
     * @return Pattern
     *
     * @throws UnsupportedPatternTypeException
     */
    public function generatePattern($type, $stepText)
    {
        foreach ($this->policies as $policy) {
            if ($policy->supportsPatternType($type)) {
                return $policy->generatePattern($stepText);
            }
        }

        throw new UnsupportedPatternTypeException(sprintf('Can not find policy for a pattern type `%s`.', $type), $type);
    }

    /**
     * Transforms pattern string to regex.
     *
     * @param string $pattern
     *
     * @return string
     *
     * @throws UnknownPatternException
     */
    public function transformPatternToRegex($pattern)
    {
        if (!isset($this->patternToRegexpCache[$pattern])) {
            $this->patternToRegexpCache[$pattern] = $this->transformPatternToRegexWithSupportedPolicy($pattern);
        }

        return $this->patternToRegexpCache[$pattern];
    }

    /**
     * @param string $pattern
     *
     * @return string
     *
     * @throws UnknownPatternException
     */
    private function transformPatternToRegexWithSupportedPolicy($pattern)
    {
        foreach ($this->policies as $policy) {
            if ($policy->supportsPattern($pattern)) {
                return $policy->transformPatternToRegex($pattern);
            }
        }

        throw new UnknownPatternException(sprintf('Can not find policy for a pattern `%s`.', $pattern), $pattern);
    }
}
