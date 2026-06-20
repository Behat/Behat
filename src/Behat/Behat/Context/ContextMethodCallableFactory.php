<?php

namespace Behat\Behat\Context;

use ReflectionMethod;

final class ContextMethodCallableFactory
{
    /**
     * @param class-string<Context> $contextClass
     */
    public static function makeCallable(string $contextClass, ReflectionMethod $method): callable
    {
        if ($method->isStatic()) {
            return [$contextClass, $method->getName()];
        }

        // NB that we create the instance with an explicit $contextClass and $methodName string, rather than passing
        // in the ReflectionMethod. This is because the method might be declared in a parent class, but we need to
        // reference it by the contextClass that will actually be instantiated / registered in the Behat process.
        return new LateBoundContextMethodCallable($contextClass, $method->getName());
    }
}
