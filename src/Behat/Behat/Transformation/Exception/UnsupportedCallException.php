<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Exception;

use Behat\Testwork\Call\Call;
use InvalidArgumentException;

/**
 * Represents an exception caused by an attempt to filter an unsupported call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UnsupportedCallException extends InvalidArgumentException implements TransformationException
{
    /**
     * @var Call
     */
    private $call;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param Call   $call
     */
    public function __construct($message, Call $call)
    {
        parent::__construct($message);

        $this->call = $call;
    }

    /**
     * Returns a call that caused exception.
     *
     * @return Call
     */
    public function getCall()
    {
        return $this->call;
    }
}
