<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformation;

use Behat\Behat\Transformation\Scope\TransformationScope;
use Behat\Behat\Transformation\SimpleArgumentTransformation;
use Behat\Testwork\Call\RuntimeCallee;
use ReflectionMethod;
use Stringable;

/**
 * Name and return type object transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TokenNameAndReturnTypeTransformation extends RuntimeCallee implements Stringable, SimpleArgumentTransformation
{
    private readonly TokenNameTransformation $tokenTransformation;
    private readonly ReturnTypeTransformation $returnTransformation;

    public static function supportsPatternAndMethod(string $pattern, ReflectionMethod $method): bool
    {
        return TokenNameTransformation::supportsPatternAndMethod($pattern, $method)
            && ReturnTypeTransformation::supportsPatternAndMethod('', $method);
    }

    /**
     * Initializes transformation.
     */
    public function __construct(string $pattern, callable $callable, ?string $description = null)
    {
        $this->tokenTransformation = new TokenNameTransformation($pattern, $callable, $description);
        $this->returnTransformation = new ReturnTypeTransformation('', $callable, $description);

        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentArgumentValue): bool
    {
        return $this->tokenTransformation->supportsDefinitionAndArgument($scope, $argumentIndex, $argumentArgumentValue)
            && $this->returnTransformation->supportsDefinitionAndArgument($scope, $argumentIndex, $argumentArgumentValue);
    }

    public function transformArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentValue): mixed
    {
        return $scope->call($this, [$argumentValue]);
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function __toString(): string
    {
        return 'NamedReturnTypeTransform ' . $this->tokenTransformation->__toString();
    }
}
