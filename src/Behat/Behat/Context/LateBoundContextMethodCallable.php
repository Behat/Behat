<?php

namespace Behat\Behat\Context;

use Behat\Testwork\Call\LateBoundInstanceCallable;
use InvalidArgumentException;
use LogicException;
use ReflectionMethod;

/**
 * Represents a `Context` method that will be called on the current instance of that Context.
 *
 * These are collected when analysing contexts for Step / Hook / Transformation definitions,
 * when we do not yet have instances of the Context classes (because those are created fresh
 * for each Scenario).
 *
 * The `InitializedContextEnvironment` will then bind this to the current Context instance
 * before it is called.
 */
final class LateBoundContextMethodCallable implements LateBoundInstanceCallable
{
    private readonly ReflectionMethod $reflection;
    private readonly string $path;

    /**
     * @param class-string<Context> $contextClass
     */
    public function __construct(
        public readonly string $contextClass,
        public readonly string $methodName,
    ) {
        $this->reflection = new ReflectionMethod($this->contextClass, $this->methodName);
        $this->path = $this->contextClass.'::'.$this->methodName.'()';
    }

    public function getReflection(): ReflectionMethod
    {
        return $this->reflection;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns a new instance bound to a specific Context class.
     */
    public function bindTo(Context $context): callable
    {
        if (!$context instanceof $this->contextClass) {
            throw new InvalidArgumentException(sprintf(
                'Cannot bind %s to an instance of %s',
                $this->path, $context::class
            ));
        }

        return $this->reflection->getClosure($context);
    }

    public function __invoke(mixed ...$args): mixed
    {
        throw new LogicException(
            sprintf('Cannot directly invoke %s - it has not been bound to a Context', $this->path)
        );
    }
}
