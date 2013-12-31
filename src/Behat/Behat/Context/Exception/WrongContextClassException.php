<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Exception;

use RuntimeException;

/**
 * Wrong context class exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class WrongContextClassException extends RuntimeException implements ContextException
{
    /**
     * @var string
     */
    private $classname;

    /**
     * Initializes exception.
     *
     * @param integer $message
     * @param string  $classname
     */
    public function __construct($message, $classname)
    {
        $this->classname = $classname;

        parent::__construct($message);
    }

    /**
     * Returns not found classname.
     *
     * @return string
     */
    public function getClassname()
    {
        return $this->classname;
    }
}
