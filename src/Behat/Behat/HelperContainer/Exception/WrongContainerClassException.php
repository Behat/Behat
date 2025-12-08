<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Exception;

use InvalidArgumentException;

/**
 * Represents an exception when provided class exists, but is not an acceptable as a container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WrongContainerClassException extends InvalidArgumentException implements HelperContainerException
{
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
