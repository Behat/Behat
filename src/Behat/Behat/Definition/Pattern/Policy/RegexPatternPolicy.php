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
use Behat\Behat\Util\StrictRegex;

use function array_keys;

/**
 * Defines a way to handle regex patterns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RegexPatternPolicy implements PatternPolicy
{
    /**
     * @var array<string,string>
     */
    private static $replacePatterns = [
        "/(?<=\W|^)\\\'(?:((?!\\').)*)\\\'(?=\W|$)/" => "'([^']*)'", // Single quoted strings
        '/(?<=\W|^)\"(?:[^\"]*)\"(?=\W|$)/' => '"([^"]*)"', // Double quoted strings
        '/(?<=\W|^)(\d+)(?=\W|$)/' => '(\\d+)', // Numbers
    ];

    public function __construct(
        private readonly StepMethodNameSuggester $methodNameSuggester = new SimpleStepMethodNameSuggester(),
    ) {
    }

    public function supportsPatternType($type): bool
    {
        return 'regex' === $type;
    }

    public function generatePattern($stepText): Pattern
    {
        $methodName = $this->methodNameSuggester->suggest(
            StrictRegex::replace(array_keys(self::$replacePatterns), '', $this->escapeStepText($stepText)),
        );
        $stepRegex = $this->generateRegex($stepText);
        $placeholderCount = $this->countPlaceholders($stepText, $stepRegex);

        return new Pattern($methodName, '/^' . $stepRegex . '$/', $placeholderCount);
    }

    public function supportsPattern($pattern): bool
    {
        return (bool) preg_match('/^(?:\\{.*\\}|([~\\/#`]).*\1)[imsxADSUXJu]*$/s', $pattern);
    }

    public function transformPatternToRegex($pattern)
    {
        if (false === @preg_match($pattern, 'anything')) {
            $error = error_get_last();
            $errorMessage = $error['message'] ?? '';

            throw new InvalidPatternException(sprintf('The regex `%s` is invalid: %s', $pattern, $errorMessage));
        }

        return $pattern;
    }

    /**
     * Generates regex from step text.
     *
     * @param string $stepText
     */
    private function generateRegex($stepText): string
    {
        return StrictRegex::replace(
            array_keys(self::$replacePatterns),
            array_values(self::$replacePatterns),
            $this->escapeStepText($stepText)
        );
    }

    /**
     * Counts regex placeholders using provided text.
     *
     * @param string $stepText
     * @param string $stepRegex
     */
    private function countPlaceholders($stepText, $stepRegex): int
    {
        preg_match('/^' . $stepRegex . '$/', $stepText, $matches);

        return count($matches) ? count($matches) - 1 : 0;
    }

    /**
     * Returns escaped step text.
     *
     * @param string $stepText
     */
    private function escapeStepText($stepText): string
    {
        return StrictRegex::replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $stepText);
    }
}
