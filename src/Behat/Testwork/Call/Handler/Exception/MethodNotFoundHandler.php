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

/**
 * Handles method not found exceptions.
 *
 * @see ExceptionHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class MethodNotFoundHandler implements ExceptionHandler
{
    const PATTERN = '/^Call to undefined method ([^:]+)::([^\)]+)\(\)$/';

    /**
     * {@inheritdoc}
     */
    final public function supportsException($exception)
    {
        if (!$exception instanceof Error) {
            return false;
        }

        return null !== $this->extractNonExistentCallable($exception);
    }

    /**
     * {@inheritdoc}
     */
    final public function handleException($exception)
    {
        $this->handleNonExistentMethod($this->extractNonExistentCallable($exception));

        return $exception;
    }

    /**
     * Override to handle non-existent method.
     *
     * @param array $callable
     */
    abstract public function handleNonExistentMethod(array $callable);

    /**
     * Extract callable from exception.
     *
     * @param Error $exception
     *
     * @return null|array
     */
    private function extractNonExistentCallable(Error $exception)
    {
        if (1 === preg_match(self::PATTERN, $exception->getMessage(), $matches)) {
            return array($matches[1], $matches[2]);
        }

        return null;
    }
}
