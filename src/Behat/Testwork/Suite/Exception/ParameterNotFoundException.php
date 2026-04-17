<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Exception;

/**
 * Represents an exception thrown when user tries to access non-existent suite parameter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ParameterNotFoundException extends SuiteException
{
    /**
     * Initializes exception.
     */
    public function __construct(
        string $message,
        string $name,
        private readonly string $parameter,
    ) {
        parent::__construct($message, $name);
    }

    /**
     * Returns parameter that caused exception.
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }
}
