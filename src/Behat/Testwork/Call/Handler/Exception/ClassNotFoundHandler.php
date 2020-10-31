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
 * Handles class not found exceptions.
 *
 * @see ExceptionHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ClassNotFoundHandler implements ExceptionHandler
{
    public const PATTERN = "/^Class (?:'|\")([^'\"]+)(?:'|\") not found$/";

    /**
     * {@inheritdoc}
     */
    final public function supportsException($exception)
    {
        if (!$exception instanceof Error) {
            return false;
        }

        return null !== $this->extractNonExistentClass($exception);
    }

    /**
     * {@inheritdoc}
     */
    final public function handleException($exception)
    {
        $this->handleNonExistentClass($this->extractNonExistentClass($exception));

        return $exception;
    }

    /**
     * Override to handle non-existent class name.
     *
     * @param string $class
     */
    abstract public function handleNonExistentClass($class);

    /**
     * Extracts missing class name from the exception.
     *
     * @param Error $exception
     *
     * @return null|string
     */
    private function extractNonExistentClass(Error $exception)
    {
        if (1 === preg_match(self::PATTERN, $exception->getMessage(), $matches)) {
            return $matches[1];
        }

        return null;
    }
}
