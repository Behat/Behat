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
 * Represents an exception when provided translation resource is not recognised.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UnknownTranslationResourceException extends InvalidArgumentException implements ContextException
{
    /**
     * Initializes exception.
     */
    public function __construct(
        string $message,
        private readonly string $resource,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns unsupported resource.
     */
    public function getResource(): string
    {
        return $this->resource;
    }
}
