<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Deprecation;

/**
 * Collects deprecations triggered by Behat code during test execution.
 */
final class DeprecationCollector
{
    private static ?DeprecationCollector $instance = null;

    /**
     * @var array<string, array{count: int, file: string, line: int}>
     */
    private array $deprecations = [];

    private int $totalCount = 0;

    /** @var callable|null */
    private $previousHandler;

    private bool $isRegistered = false;

    /**
     * Returns the singleton instance, creating it if necessary.
     */
    public static function getInstance(): self
    {
        if (!self::$instance instanceof DeprecationCollector) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Records a deprecation.
     *
     * By default the deprecation is reported against the caller. Pass $file and $line to report it against
     * the code that is responsible for the deprecated usage instead, e.g. a user's step definition.
     */
    public static function trigger(string $message, ?string $file = null, ?int $line = null): void
    {
        $instance = self::getInstance();

        if ($file === null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $caller = $backtrace[0];

            $file = $caller['file'] ?? 'unknown';
            $line = $caller['line'] ?? 0;
        }

        $instance->recordDeprecation($message, $file, $line ?? 0);
    }

    /**
     * Registers the deprecation error handler.
     */
    public function register(): void
    {
        if ($this->isRegistered) {
            return;
        }

        $this->previousHandler = set_error_handler($this->createErrorHandler());
        $this->isRegistered = true;
    }

    /**
     * Unregisters the deprecation error handler.
     */
    public function unregister(): void
    {
        if (!$this->isRegistered) {
            return;
        }

        restore_error_handler();
        $this->isRegistered = false;
    }

    /**
     * Returns all collected deprecations with their counts and locations.
     *
     * @return array<string, array{count: int, file: string, line: int}>
     */
    public function getDeprecations(): array
    {
        return $this->deprecations;
    }

    /**
     * Returns the total number of deprecation occurrences.
     */
    public function getDeprecationCount(): int
    {
        return $this->totalCount;
    }

    /**
     * Checks if any deprecations were collected.
     */
    public function hasDeprecations(): bool
    {
        return $this->totalCount > 0;
    }

    /**
     * Creates the error handler that captures deprecations.
     */
    private function createErrorHandler(): callable
    {
        return function (int $errno, string $errstr, string $errfile, int $errline): bool {
            if ($errno !== E_USER_DEPRECATED && $errno !== E_DEPRECATED) {
                if ($this->previousHandler !== null) {
                    return ($this->previousHandler)($errno, $errstr, $errfile, $errline);
                }

                return false;
            }

            $this->recordDeprecation($errstr, $errfile, $errline);

            return true;
        };
    }

    private function recordDeprecation(string $message, string $file, int $line): void
    {
        if (isset($this->deprecations[$message])) {
            ++$this->deprecations[$message]['count'];
        } else {
            $this->deprecations[$message] = ['count' => 1, 'file' => $file, 'line' => $line];
        }

        ++$this->totalCount;
    }
}
