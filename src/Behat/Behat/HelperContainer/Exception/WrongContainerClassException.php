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
    /**
     * @var string
     */
    private $class;

    public function __construct(string $message, string $class)
    {
        $this->class = $class;

        parent::__construct($message);
    }

    /**
     * Returns not found classname.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}
