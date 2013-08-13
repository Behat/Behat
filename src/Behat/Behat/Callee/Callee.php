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
    private $method;
    private $callable;
    private $path;
    private $description;

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

        if ($this->method = is_array($callable)) {
            $this->path = $callable[0] . '::' . $callable[1] . '()';
        } else {
            $reflection = new ReflectionFunction($callable);
            $this->path = $reflection->getFileName() . ':' . $reflection->getStartLine();
        }

        $this->callable = $callable;
        $this->description = $description;
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
     * Returns callee description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns true if callee is a method, false otherwise.
     *
     * @return Boolean
     */
    public function isMethod()
    {
        return $this->method;
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
     * Returns callable reflection.
     *
     * @return ReflectionFunction|ReflectionMethod
     */
    public function getReflection()
    {
        if ($this->isMethod()) {
            return new ReflectionMethod($this->callable[0], $this->callable[1]);
        }

        return new ReflectionFunction($this->callable);
    }
}
