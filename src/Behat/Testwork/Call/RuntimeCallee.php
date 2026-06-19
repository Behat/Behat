<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use Behat\Behat\Context\Context;
use Behat\Testwork\Call\Exception\BadCallbackException;
use Behat\Testwork\Deprecation\DeprecationCollector;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Represents callee created and executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @phpstan-type TBehatCallable callable|array{class-string<Context>, string}
 */
class RuntimeCallee implements Callee
{
    /**
     * @var TBehatCallable
     */
    private $callable;
    private ReflectionMethod|ReflectionFunction $reflection;
    private string $path;

    /**
     * Initializes callee.
     */
    public function __construct(
        callable|array $callable,
        private readonly ?string $description = null,
    ) {
        if (is_array($callable)) {
            if (!$this->isCallableOrBehatContextCallable($callable)) {
                DeprecationCollector::trigger('Creating '.static::class.' with a non-callable array other than a Behat context method reference is deprecated');
            }
            $this->reflection = new ReflectionMethod($callable[0], $callable[1]);
            $this->path = $callable[0] . '::' . $callable[1] . '()';
        } else {
            $this->reflection = new ReflectionFunction($callable);
            $this->path = $this->reflection->getFileName() . ':' . $this->reflection->getStartLine();
        }

        $this->callable = $callable;
    }

    private function isCallableOrBehatContextCallable(array $callable): bool
    {
        if (is_callable($callable)) {
            return true;
        }

        return count($callable) === 2
            && is_string($callable[0])
            && is_string($callable[1])
            && is_a($callable[0], Context::class, true);
    }

    /**
     * Returns callee description.
     *
     * @return ?string
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
     * Returns callable.
     *
     * @return TBehatCallable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Returns callable reflection.
     *
     * @return ReflectionFunctionAbstract
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * Returns true if callee is a method, false otherwise.
     *
     * @return bool
     */
    public function isAMethod()
    {
        return $this->reflection instanceof ReflectionMethod;
    }

    /**
     * Returns true if callee is an instance (non-static) method, false otherwise.
     *
     * @return bool
     */
    public function isAnInstanceMethod()
    {
        return $this->reflection instanceof ReflectionMethod
            && !$this->reflection->isStatic();
    }

    /**
     * @param callable|array{class-string, string} $callable
     *
     * @deprecated see throwIfCallableIsInstanceMethod()
     */
    protected function throwIfInstanceMethod(callable|array $callable, string $hookType): void
    {
        DeprecationCollector::trigger('throwIfInstanceMethod() is deprecated and will be removed in 4.0 - use throwIfCallableIsInstanceMethod');
        if ($callable !== $this->callable) {
            // They have passed a different callable to the one we were initialised with.
            // Wrap it in a new RuntimeCallee so that we can check it.
            (new RuntimeCallee($callable))->throwIfCallableIsInstanceMethod($hookType);
        } else {
            $this->throwIfCallableIsInstanceMethod($hookType);
        }
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
