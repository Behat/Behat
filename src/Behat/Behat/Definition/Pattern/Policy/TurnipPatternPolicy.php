<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern\Policy;

use Behat\Behat\Definition\Exception\InvalidPatternException;
use Behat\Behat\Definition\Pattern\Pattern;
use Behat\Behat\Definition\Pattern\SimpleStepMethodNameSuggester;
use Behat\Behat\Definition\Pattern\StepMethodNameSuggester;

use function preg_replace;

/**
 * Defines a way to handle turnip patterns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TurnipPatternPolicy implements PatternPolicy
{
    public const TOKEN_REGEX = "[\"']?(?P<%s>(?<=\")[^\"]*(?=\")|(?<=')[^']*(?=')|\-?[\w\.\,]+)['\"]?";

    public const PLACEHOLDER_REGEXP = "/\\\:(\w+)/";
    public const OPTIONAL_WORD_REGEXP = '/(\s)?\\\\\(([^\\\]+)\\\\\)(\s)?/';
    public const ALTERNATIVE_WORD_REGEXP = '/(\w+)\\\\\/(\w+)/';

    /**
     * @var string[]
     */
    private $regexCache = [];

    /**
     * @var string[]
     */
    private static $placeholderPatterns = [
        "/(?<!\w)\"[^\"]+\"(?!\w)/",
        "/(?<!\w)'[^']+'(?!\w)/",
        "/(?<!\w|\.|\,)\-?\d+(?:[\.\,]\d+)?(?!\w|\.|\,)/",
    ];

    public function __construct(
        private readonly StepMethodNameSuggester $methodNameSuggester = new SimpleStepMethodNameSuggester(),
    ) {
    }

    public function supportsPatternType($type): bool
    {
        return null === $type || 'turnip' === $type;
    }

    public function generatePattern($stepText): Pattern
    {
        $count = 0;
        $pattern = $stepText;
        foreach (self::$placeholderPatterns as $replacePattern) {
            $pattern = preg_replace_callback(
                $replacePattern,
                function () use (&$count) { return ':arg' . ++$count; },
                (string) $pattern
            );
        }
        $pattern = $this->escapeAlternationSyntax($pattern);
        $methodName = $this->methodNameSuggester->suggest(
            preg_replace(self::$placeholderPatterns, '', $stepText),
        );

        return new Pattern($methodName, $pattern, $count);
    }

    public function supportsPattern($pattern): bool
    {
        return true;
    }

    public function transformPatternToRegex($pattern)
    {
        if (!isset($this->regexCache[$pattern])) {
            $this->regexCache[$pattern] = $this->createTransformedRegex($pattern);
        }

        return $this->regexCache[$pattern];
    }

    /**
     * @param string $pattern
     */
    private function createTransformedRegex($pattern): string
    {
        $regex = preg_quote($pattern, '/');

        $regex = $this->replaceTokensWithRegexCaptureGroups($regex);
        $regex = $this->replaceTurnipOptionalEndingWithRegex($regex);
        $regex = $this->replaceTurnipAlternativeWordsWithRegex($regex);

        return '/^' . $regex . '$/iu';
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
            [$this, 'replaceTokenWithRegexCaptureGroup'],
            $regex
        );
    }

    private function replaceTokenWithRegexCaptureGroup($tokenMatch): string
    {
        if (strlen((string) $tokenMatch[1]) > 32) {
            throw new InvalidPatternException(
                "Token name should not exceed 32 characters, but `{$tokenMatch[1]}` was used."
            );
        }

        return sprintf(self::TOKEN_REGEX, $tokenMatch[1]);
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
     */
    private function replaceTurnipAlternativeWordsWithRegex($regex): string
    {
        $regex = preg_replace(self::ALTERNATIVE_WORD_REGEXP, '(?:\1|\2)', $regex);
        $regex = $this->removeEscapingOfAlternationSyntax($regex);

        return $regex;
    }

    /**
     * Adds escaping to alternation syntax in pattern.
     *
     * By default, Turnip treats `/` as alternation syntax. Meaning `one/two` for Turnip
     * means either `one` or `two`. Sometimes though you'll want to use slash character
     * with different purpose (URL, UNIX paths). In this case, you would escape slashes
     * with backslash.
     *
     * This method adds escaping to all slashes in generated snippets.
     *
     * @param string $pattern
     */
    private function escapeAlternationSyntax($pattern): string
    {
        return str_replace('/', '\/', $pattern);
    }

    /**
     * Removes escaping of alternation syntax from regex.
     *
     * This method removes those escaping backslashes from your slashes, so your steps
     * could be matched against your escaped definitions.
     *
     * @param string $regex
     */
    private function removeEscapingOfAlternationSyntax($regex): string
    {
        return str_replace('\\\/', '/', $regex);
    }
}
