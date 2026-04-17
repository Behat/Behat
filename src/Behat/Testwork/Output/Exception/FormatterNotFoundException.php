<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Exception;

use InvalidArgumentException;

/**
 * Represents an exception thrown because requested formatter is not found.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FormatterNotFoundException extends InvalidArgumentException implements OutputException
{
    /**
     * Initializes exception.
     */
    public function __construct(
        string $message,
        private readonly string $name,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns formatter name.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
