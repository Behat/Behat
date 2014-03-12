<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Exception;

use InvalidArgumentException;

/**
 * Represents exception caused by a bad callback.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BadCallbackException extends InvalidArgumentException implements CallException
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * Initializes exception.
     *
     * @param string   $message
     * @param callable $callable
     */
    public function __construct($message, $callable)
    {
        $this->callable = $callable;

        parent::__construct($message);
    }

    /**
     * Returns callback that caused exception.
     *
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }
}
