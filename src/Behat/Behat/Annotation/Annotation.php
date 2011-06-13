<?php

namespace Behat\Behat\Annotation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat annotation class.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Annotation implements AnnotationInterface
{
    private $callback;

    /**
     * Constructs annotation.
     *
     * @param   array   $callback
     */
    public function __construct(array $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback should be valid callable');
        }

        $this->callback = $callback;
    }

    /**
     * @see Behat\Behat\Annotation\AnnotationInterface::getCallback
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @see Behat\Behat\Annotation\AnnotationInterface::getClass
     */
    public function getClass()
    {
        return $this->callback[0];
    }

    /**
     * @see Behat\Behat\Annotation\AnnotationInterface::getMethod
     */
    public function getMethod()
    {
        return $this->callback[1];
    }

    /**
     * @see Behat\Behat\Annotation\AnnotationInterface::getCallbackReflection
     */
    public function getCallbackReflection()
    {
        return new \ReflectionMethod($this->getClass(), $this->getMethod());
    }
}
