<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformation;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Behat\Transformation\SimpleArgumentTransformation;
use Behat\Testwork\Call\CallCenter;
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
    /**
     * @var TokenNameTransformation
     */
    private $tokenTransformation;
    /**
     * @var ReturnTypeTransformation
     */
    private $returnTransformation;

    public static function supportsPatternAndMethod($pattern, ReflectionMethod $method): bool
    {
        return TokenNameTransformation::supportsPatternAndMethod($pattern, $method)
            && ReturnTypeTransformation::supportsPatternAndMethod('', $method);
    }

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct($pattern, $callable, $description = null)
    {
        $this->tokenTransformation = new TokenNameTransformation($pattern, $callable, $description);
        $this->returnTransformation = new ReturnTypeTransformation('', $callable, $description);

        parent::__construct($callable, $description);
    }

    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentArgumentValue): bool
    {
        return $this->tokenTransformation->supportsDefinitionAndArgument($definitionCall, $argumentIndex, $argumentArgumentValue)
            && $this->returnTransformation->supportsDefinitionAndArgument($definitionCall, $argumentIndex, $argumentArgumentValue);
    }

    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $call = new TransformationCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getCallee(),
            $this,
            [$argumentValue]
        );

        $result = $callCenter->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getPattern()
    {
        return $this->tokenTransformation->getPattern();
    }

    public function __toString()
    {
        return 'NamedReturnTypeTransform ' . $this->getPattern();
    }
}
