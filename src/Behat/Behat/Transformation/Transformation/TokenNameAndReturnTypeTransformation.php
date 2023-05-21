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

/**
 * Name and return type object transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TokenNameAndReturnTypeTransformation extends RuntimeCallee implements SimpleArgumentTransformation
{
    /**
     * @var TokenNameTransformation
     */
    private $tokenTransformation;
    /**
     * @var ReturnTypeTransformation
     */
    private $returnTransformation;

    /**
     * {@inheritdoc}
     */
    static public function supportsPatternAndMethod($pattern, ReflectionMethod $method)
    {
        return TokenNameTransformation::supportsPatternAndMethod($pattern, $method)
            && ReturnTypeTransformation::supportsPatternAndMethod('', $method);
    }

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($pattern, $callable, $description = null)
    {
        $this->tokenTransformation = new TokenNameTransformation($pattern, $callable, $description);
        $this->returnTransformation = new ReturnTypeTransformation('', $callable, $description);

        parent::__construct($callable, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentArgumentValue)
    {
        return $this->tokenTransformation->supportsDefinitionAndArgument($definitionCall, $argumentIndex, $argumentArgumentValue)
            && $this->returnTransformation->supportsDefinitionAndArgument($definitionCall, $argumentIndex, $argumentArgumentValue);
    }

    /**
     * {@inheritdoc}
     */
    public function transformArgument(CallCenter $callCenter, DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $call = new TransformationCall(
            $definitionCall->getEnvironment(),
            $definitionCall->getCallee(),
            $this,
            array($argumentValue)
        );

        $result = $callCenter->makeCall($call);

        if ($result->hasException()) {
            throw $result->getException();
        }

        return $result->getReturn();
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 100;
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern()
    {
        return $this->tokenTransformation->getPattern();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'NamedReturnTypeTransform ' . $this->getPattern();
    }
}
