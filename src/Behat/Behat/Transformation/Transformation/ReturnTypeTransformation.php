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
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionNamedType;

/**
 * By-type object transformation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ReturnTypeTransformation extends RuntimeCallee implements SimpleArgumentTransformation
{

    /**
     * {@inheritdoc}
     */
    static public function supportsPatternAndMethod($pattern, ReflectionMethod $method)
    {
        $returnClass = self::getReturnClass($method);

        if (null === $returnClass) {
            return false;
        }

        return '' === $pattern;
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
        parent::__construct($callable, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentArgumentValue)
    {
        $returnClass = self::getReturnClass($this->getReflection());

        if (null === $returnClass) {
            return false;
        }

        $parameterClass = $this->getParameterClassNameByIndex($definitionCall, $argumentIndex);

        return $parameterClass === $returnClass;
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
        return 80;
    }

    /**
     * {@inheritdoc}
     */
    public function getPattern()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'ReturnTypeTransform';
    }

    /**
     * Extracts parameters from provided definition call.
     *
     * @param ReflectionFunctionAbstract $reflection
     *
     * @return null|string
     */
    static private function getReturnClass(ReflectionFunctionAbstract $reflection)
    {
        $type = $reflection->getReturnType();

        // Skip ReflectionUnionType as they can't be relied on for a transform
        if (null === $type || !($type instanceof \ReflectionNamedType) || $type->isBuiltin()) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        return (string) $type;
    }

    /**
     * Attempts to get definition parameter using its index (parameter position or name).
     *
     * @param DefinitionCall $definitionCall
     * @param string|integer $argumentIndex
     *
     * @return null|string
     */
    private function getParameterClassNameByIndex(DefinitionCall $definitionCall, $argumentIndex)
    {
        $parameters = array_filter(
            array_filter($this->getCallParameters($definitionCall),
                $this->hasIndex($argumentIndex)
            ),
            $this->getClassReflection()
        );

        if (count($parameters) == 0) {
            return null;
        }

        return ($this->getClassReflection())(current($parameters))->getName();
    }

    /**
     * Extracts parameters from provided definition call.
     *
     * @param DefinitionCall $definitionCall
     *
     * @return ReflectionParameter[]
     */
    private function getCallParameters(DefinitionCall $definitionCall)
    {
        return $definitionCall->getCallee()->getReflection()->getParameters();
    }

    /**
     * Returns appropriate closure for filtering parameter by index.
     *
     * @param string|integer $index
     *
     * @return Closure
     */
    private function hasIndex($index)
    {
        return is_string($index) ? $this->hasName($index) : $this->hasPosition($index);
    }

    /**
     * Returns closure to filter parameter by name.
     *
     * @param string $index
     *
     * @return Closure
     */
    private function hasName($index)
    {
        return function (ReflectionParameter $parameter) use ($index) {
            return $index === $parameter->getName();
        };
    }

    /**
     * Returns closure to filter parameter by position.
     *
     * @param integer $index
     *
     * @return Closure
     */
    private function hasPosition($index)
    {
        return function (ReflectionParameter $parameter) use ($index) {
            return $index === $parameter->getPosition();
        };
    }

    /**
     * Returns closure to filter parameter by typehinted class.
     *
     * @return Closure
     */
    private function getClassReflection() : closure
    {
        return function (ReflectionParameter $parameter) : ?ReflectionClass
        {
            $t = $parameter->getType();

            if ($t instanceof ReflectionNamedType) {
                try {
                    return new ReflectionClass($t->getName());
                }
                catch (ReflectionException $t) {
                    return null;
                }
            }

            return null;
        };
    }
}
