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
 * Wrong context class exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class WrongContextClassException extends InvalidArgumentException implements ContextException
{
    /**
     * @var string
     */
    private $class;

    /**
     * Initializes exception.
     *
     * @param integer $message
     * @param string  $class
     */
    public function __construct($message, $class)
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
