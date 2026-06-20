<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Testwork\Call\CallCenter;
use ReflectionMethod;

/**
 * Represents a simple self-contained transformation capable of changing a single argument.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface SimpleArgumentTransformation extends Transformation
{
    public function __construct(string $pattern, callable $callable, ?string $description = null);

    /**
     * Checks if transformation supports given pattern.
     */
    public static function supportsPatternAndMethod(string $pattern, ReflectionMethod $method): bool;

    /**
     * Returns transformation priority.
     */
    public function getPriority(): int;

    /**
     * Checks if transformation supports argument.
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, int|string $argumentIndex, mixed $argumentArgumentValue): bool;

    /**
     * Transforms argument value using transformation and returns a new one.
     */
    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, int|string $argumentIndex, mixed $argumentValue): mixed;
}
