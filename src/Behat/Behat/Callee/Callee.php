<?php

namespace Behat\Behat\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Core callee class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Callee implements CalleeInterface
{
    private $callable;
    private $description;
    private $reflection;
    private $path;

    /**
     * Initializes callee.
     *
     * @param Callable    $callable
     * @param null|string $description
     *
     * @throws InvalidArgumentException If invalid callback provided
     */
    public function __construct($callable, $description = null)
    {
        if (!is_array($callable) && !is_callable($callable)) {
            throw new InvalidArgumentException(sprintf(
                '%s callback should be a valid callable, but %s given',
                get_class($this),
                gettype($callable)
            ));
        }

        if (is_array($callable)) {
            $this->reflection = new ReflectionMethod($callable[0], $callable[1]);
            $this->path = $callable[0] . '::' . $callable[1] . '()';
        } else {
            $this->reflection = new ReflectionFunction($callable);
            $this->path = $this->reflection->getFileName() . ':' . $this->reflection->getStartLine();
        }

        $this->callable = $callable;
        $this->description = $description;
    }

    /**
     * Returns callable.
     *
     * @return Callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Returns callee description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns callee definition path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns true if callee is a method, false otherwise.
     *
     * @return Boolean
     */
    public function isMethod()
    {
        return $this->reflection instanceof ReflectionMethod;
    }

    /**
     * Returns callable reflection.
     *
     * @return ReflectionFunction|ReflectionMethod
     */
    public function getReflection()
    {
        return $this->reflection;
    }
}
