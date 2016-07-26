<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Exception;

use InvalidArgumentException;

/**
 * Represents an exception caused by usage of type-hinted step definition argument without factory method.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FactoryMethodNotFound extends InvalidArgumentException implements TransformationException
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $method;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string   $class
     */
    public function __construct($message, $class, $method)
    {
        parent::__construct($message);

        $this->class = $class;
        $this->method = $method;
    }

    /**
     * Returns class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Return factory method name.
     *
     * @return string
     */
    public function getFactoryMethod()
    {
        return $this->method;
    }
}
