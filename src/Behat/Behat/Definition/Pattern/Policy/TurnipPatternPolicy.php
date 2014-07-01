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
use Behat\Transliterator\Transliterator;

/**
 * Defines a way to handle turnip patterns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TurnipPatternPolicy implements PatternPolicy
{
    const TOKEN_REGEX = "[\"']?(?P<%s>(?<=\")[^\"]+(?=\")|(?<=')[^']+(?=')|(?<=\W|^)\w+(?:[\.\,]\w+)?(?=\W|$))['\"]?";

    const PLACEHOLDER_REGEXP = "/\\\:(\w+)/";
    const OPTIONAL_WORD_REGEXP = '/(\s)?\\\\\(([^\\\]+)\\\\\)(\s)?/';
    const ALTERNATIVE_WORD_REGEXP = '/(\w+)\\\\\/(\w+)/';

    /**
     * @var string[]
     */
    private static $placeholderPatterns = array(
        "/(?<=\W|^)\"[^\"]+\"(?=\W|$)/",
        "/(?<=\W|^)'[^']+'(?=\W|$)/",
        "/(?<=\W|^)\d+(?:[\.\,]\d+)?(?=\W|$)/"
    );

    /**
     * {@inheritdoc}
     */
    public function supportsPatternType($type)
    {
        return null === $type || 'turnip' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePattern($stepText)
    {
        $count = 0;
        $pattern = $stepText;
        foreach (self::$placeholderPatterns as $replacePattern) {
            $pattern = preg_replace_callback(
                $replacePattern,
                function () use (&$count) { return ':arg' . ++$count; },
                $pattern
            );
        }
        $canonicalText = $this->generateCanonicalText($stepText);

        return new Pattern($canonicalText, $pattern, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsPattern($pattern)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function transformPatternToRegex($pattern)
    {
        $regex = preg_quote($pattern, '/');

        $regex = $this->replaceTokensWithRegexCaptureGroups($regex);
        $regex = $this->replaceTurnipOptionalEndingWithRegex($regex);
        $regex = $this->replaceTurnipAlternativeWordsWithRegex($regex);

        return '/^' . $regex . '$/i';
    }

    /**
     * Generates canonical text for step text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function generateCanonicalText($stepText)
    {
        $canonicalText = preg_replace(self::$placeholderPatterns, '', $stepText);
        $canonicalText = Transliterator::transliterate($canonicalText, ' ');
        $canonicalText = preg_replace('/[^a-zA-Z\_\ ]/', '', $canonicalText);
        $canonicalText = str_replace(' ', '', ucwords($canonicalText));

        return $canonicalText;
    }

    /**
     * Replaces turnip tokens with regex capture groups.
     *
     * @param string $regex
     *
     * @return string
     */
    private function replaceTokensWithRegexCaptureGroups($regex)
    {
        $tokenRegex = self::TOKEN_REGEX;

        return preg_replace_callback(
            self::PLACEHOLDER_REGEXP,
            function ($match) use ($tokenRegex) { return sprintf($tokenRegex, $match[1]); },
            $regex
        );
    }

    /**
     * Replaces turnip optional ending with regex non-capturing optional group.
     *
     * @param string $regex
     *
     * @return string
     */
    private function replaceTurnipOptionalEndingWithRegex($regex)
    {
        return preg_replace(self::OPTIONAL_WORD_REGEXP, '(?:\1)?(?:\2)?(?:\3)?', $regex);
    }

    /**
     * Replaces turnip alternative words with regex non-capturing alternating group.
     *
     * @param string $regex
     *
     * @return string
     */
    private function replaceTurnipAlternativeWordsWithRegex($regex)
    {
        return preg_replace(self::ALTERNATIVE_WORD_REGEXP, '(?:\1|\2)', $regex);
    }
}
