<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Exception;

use RuntimeException;

/**
 * Bad callback exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BadCallbackException extends RuntimeException implements CallException
{
    /**
     * @var callback
     */
    private $callback;

    /**
     * Initializes exception.
     *
     * @param string   $message
     * @param callback $call
     */
    public function __construct($message, $call)
    {
        $this->callback = $call;

        parent::__construct($message);
    }

    /**
     * Returns callback that caused exception.
     *
     * @return callback
     */
    public function getCallback()
    {
        return $this->callback;
    }
}
