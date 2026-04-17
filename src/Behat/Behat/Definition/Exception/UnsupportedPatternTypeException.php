<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use InvalidArgumentException;

/**
 * Represents an exception caused by an unsupported pattern type.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UnsupportedPatternTypeException extends InvalidArgumentException implements DefinitionException
{
    /**
     * Initializes exception.
     */
    public function __construct(
        string $message,
        private readonly string $type,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns pattern type that caused exception.
     */
    public function getType(): string
    {
        return $this->type;
    }
}
