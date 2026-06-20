<?php

namespace Behat\Testwork\Call;

use ReflectionMethod;

/**
 * Represents a "callable" method that will be bound to an object instance later in execution.
 *
 * For example, Behat implements this interface when reading step definitions, hooks, and transformations
 * that will be bound to a new instance of the declaring `Context` for each scenario.
 */
interface LateBoundInstanceCallable
{
    /**
     * The Reflection for the method that will ultimately be called.
     */
    public function getReflection(): ReflectionMethod;

    /**
     * A path for the method e.g. to display in error messages, logs and output.
     */
    public function getPath(): string;

    /**
     * Invokes the method. This may throw if the method has not yet been bound to an instance.
     */
    public function __invoke(mixed ...$args): mixed;
}
