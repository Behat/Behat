<?php

declare(strict_types=1);

namespace Behat\Behat\Util;

use InvalidArgumentException;

/**
 * @internal
 */
final class StrictRegex
{
    /**
     * @param string|list<string> $pattern
     * @param string|list<string> $replacement
     *
     * @throws RegexException if the regex fails
     * @throws InvalidArgumentException if passing pattern & replacements as arrays and the number of entries does not match
     */
    public static function replace(
        string|array $pattern,
        string|array $replacement,
        string $subject,
        int $limit = -1,
    ): string {
        if (is_array($pattern) && is_array($replacement) && count($pattern) !== count($replacement)) {
            // If the number of replacements does not match the number of patterns, PHP treats missing replacements as
            // empty strings. However, it is safer to force the caller to be explicit about this.
            throw new InvalidArgumentException(
                sprintf('Expected %d entries in $replacement array, got %d', count($pattern), count($replacement)),
            );
        }

        $result = self::callWithErrorCapture(fn (): ?string => preg_replace($pattern, $replacement, $subject, $limit));
        if ($result === null) {
            throw new RegexException('Regex failed: '.preg_last_error_msg(), preg_last_error());
        }

        return $result;
    }

    /**
     * @param string|list<string> $pattern
     * @param callable(array<mixed,string>):string $callback
     *
     * @throws RegexException if the regex fails
     */
    public static function replaceCallback(
        array|string $pattern,
        callable $callback,
        string $subject,
    ): string {
        $result = self::callWithErrorCapture(fn (): ?string => preg_replace_callback($pattern, $callback, $subject));
        if ($result === null) {
            throw new RegexException('Regex failed: '.preg_last_error_msg(), preg_last_error());
        }

        return $result;
    }

    /**
     * @template T of mixed
     *
     * @param callable():T $callable
     *
     * @return T
     */
    private static function callWithErrorCapture(callable $callable): mixed
    {
        set_error_handler(fn ($level, $msg) => throw new RegexException($msg, $level));
        try {
            return $callable();
        } finally {
            restore_error_handler();
        }
    }
}
