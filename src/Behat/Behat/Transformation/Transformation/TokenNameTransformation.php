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
 * Token name based transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TokenNameTransformation extends RuntimeCallee implements Stringable, SimpleArgumentTransformation
{
    public const PATTERN_REGEX = '/^\:\w+$/';

    public static function supportsPatternAndMethod(string $pattern, ReflectionMethod $method): bool
    {
        return 1 === preg_match(self::PATTERN_REGEX, (string) $pattern);
    }

    /**
     * Initializes transformation.
     */
    public function __construct(
        private readonly string $pattern,
        callable $callable,
        ?string $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentArgumentValue): bool
    {
        return ':' . $argumentIndex === $this->pattern;
    }

    public function transformArgument(TransformationScope $scope, int|string $argumentIndex, mixed $argumentValue): mixed
    {
        return $scope->call($this, [$argumentValue]);
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function __toString(): string
    {
        return 'TokenNameTransform ' . $this->pattern;
    }
}
