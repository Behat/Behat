<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Tests\Fixtures;

use Exception;

/**
 * Minimal assertions for the context classes under tests/Fixtures.
 *
 * Behat's own test suite deliberately does not depend on an assertion library, so that we do not couple our runtime
 * code to PHPUnit. Each assertion throws a plain exception whose message carries everything we want to report, which
 * is what the fixtures' expected output asserts on.
 *
 * See https://github.com/Behat/Behat/issues/1746 for background.
 */
final class Assert
{
    /**
     * Asserts that two values are equal, comparing loosely as the fixtures rely on comparing step arguments
     * (which are always strings) against typed context state.
     */
    public static function assertEquals(mixed $expected, mixed $actual): void
    {
        if ($expected == $actual) {
            return;
        }

        throw new Exception(sprintf(
            'Failed asserting that %s matches expected %s.',
            self::describe($actual),
            self::describe($expected),
        ));
    }

    public static function assertSame(mixed $expected, mixed $actual): void
    {
        if ($expected === $actual) {
            return;
        }

        throw new Exception(sprintf(
            'Failed asserting that %s is identical to %s.',
            self::describe($actual),
            self::describe($expected),
        ));
    }

    public static function assertNull(mixed $actual): void
    {
        if ($actual === null) {
            return;
        }

        throw new Exception(sprintf('Failed asserting that %s is null.', self::describe($actual)));
    }

    public static function assertIsArray(mixed $actual): void
    {
        if (is_array($actual)) {
            return;
        }

        throw new Exception(sprintf('Failed asserting that %s is of type array.', self::describe($actual)));
    }

    public static function assertIsString(mixed $actual): void
    {
        if (is_string($actual)) {
            return;
        }

        throw new Exception(sprintf('Failed asserting that %s is of type string.', self::describe($actual)));
    }

    /**
     * @param array<mixed> $haystack
     */
    public static function assertContains(mixed $needle, array $haystack): void
    {
        if (in_array($needle, $haystack, true)) {
            return;
        }

        throw new Exception(sprintf(
            'Failed asserting that %s contains %s.',
            self::describe($haystack),
            self::describe($needle),
        ));
    }

    /**
     * @param array<mixed> $haystack
     */
    public static function assertNotContains(mixed $needle, array $haystack): void
    {
        if (!in_array($needle, $haystack, true)) {
            return;
        }

        throw new Exception(sprintf(
            'Failed asserting that %s does not contain %s.',
            self::describe($haystack),
            self::describe($needle),
        ));
    }

    /**
     * Renders a value the way the expected output of our features describes it.
     */
    private static function describe(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_string($value) => "'" . $value . "'",
            is_scalar($value) => (string) $value,
            default => (string) json_encode($value),
        };
    }
}
