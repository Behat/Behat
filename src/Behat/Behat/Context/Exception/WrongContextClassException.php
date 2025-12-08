<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Exception;

use InvalidArgumentException;

/**
 * Represents an exception when provided class exists, but is not an acceptable as a context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WrongContextClassException extends InvalidArgumentException implements ContextException
{
    /**
     * Initializes exception.
     */
    public function __construct(
        string $message,
        private readonly string $class,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns not found classname.
     */
    public function getClass(): string
    {
        return $this->class;
    }
}
