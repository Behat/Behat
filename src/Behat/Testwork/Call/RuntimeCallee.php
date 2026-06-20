<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use Behat\Testwork\Call\Exception\BadCallbackException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Represents callee created and executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeCallee implements Callee
{
    /**
     * @var callable
     */
    private $callable;
    private ReflectionMethod|ReflectionFunction $reflection;
    private string $path;

    /**
     * Initializes callee.
     */
    public function __construct(
        callable $callable,
        private readonly ?string $description = null,
    ) {
        if ($callable instanceof LateBoundInstanceCallable) {
            $this->reflection = $callable->getReflection();
            $this->path = $callable->getPath();
        } elseif (is_array($callable)) {
            // An array `callable` will always have 2 elements, [className-or-object, methodName]
            $this->reflection = new ReflectionMethod($callable[0], $callable[1]);
            $this->path = $callable[0].'::'.$callable[1].'()';
        } else {
            $this->reflection = new ReflectionFunction($callable);
            $this->path = $this->reflection->getFileName() . ':' . $this->reflection->getStartLine();
        }

        $this->callable = $callable;
    }

    /**
     * Returns callee description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Returns callee definition path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns callable.
     */
    public function getCallable(): callable
    {
        return $this->callable;
    }

    /**
     * Returns callable reflection.
     */
    public function getReflection(): ReflectionFunctionAbstract
    {
        return $this->reflection;
    }

    /**
     * Returns true if callee is a method, false otherwise.
     */
    public function isAMethod(): bool
    {
        return $this->reflection instanceof ReflectionMethod;
    }

    /**
     * Returns true if callee is an instance (non-static) method, false otherwise.
     */
    public function isAnInstanceMethod(): bool
    {
        return $this->reflection instanceof ReflectionMethod
            && !$this->reflection->isStatic();
    }

    final protected function throwIfCallableIsInstanceMethod(string $hookType): void
    {
        if ($this->isAnInstanceMethod()) {
            throw new BadCallbackException(sprintf(
                '%s hook callback: %s must be a static method',
                $hookType,
                $this->getPath(),
            ), $this->getCallable());
        }
    }
}
