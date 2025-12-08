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
 * Represents an exception caused by an unrecognised definition pattern.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UnknownPatternException extends InvalidArgumentException implements DefinitionException
{
    public function __construct(
        string $message,
        private readonly string $pattern,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns pattern that caused exception.
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }
}
