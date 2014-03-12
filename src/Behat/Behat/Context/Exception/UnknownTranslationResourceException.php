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
     * @var string
     */
    private $resource;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $class
     */
    public function __construct($message, $class)
    {
        $this->resource = $class;

        parent::__construct($message);
    }

    /**
     * Returns unsupported resource.
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }
}
