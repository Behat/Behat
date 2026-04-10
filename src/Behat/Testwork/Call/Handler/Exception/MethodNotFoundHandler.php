<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler\Exception;

use Behat\Testwork\Call\Handler\ExceptionHandler;
use Error;
use Throwable;

/**
 * Handles method not found exceptions.
 *
 * @see ExceptionHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class MethodNotFoundHandler implements ExceptionHandler
{
    public const PATTERN = '/^Call to undefined method ([^:]+)::([^\)]+)\(\)$/';

    final public function supportsException(Throwable $exception): bool
    {
        if (!$exception instanceof Error) {
            return false;
        }

        return null !== $this->extractNonExistentCallable($exception);
    }

    final public function handleException(Throwable $exception): Throwable
    {
        assert($exception instanceof Error);
        $this->handleNonExistentMethod($this->extractNonExistentCallable($exception));

        return $exception;
    }

    /**
     * Override to handle non-existent method.
     */
    abstract public function handleNonExistentMethod(array $callable);

    /**
     * Extract callable from exception.
     */
    private function extractNonExistentCallable(Error $exception): ?array
    {
        if (1 === preg_match(self::PATTERN, $exception->getMessage(), $matches)) {
            return [$matches[1], $matches[2]];
        }

        return null;
    }
}
