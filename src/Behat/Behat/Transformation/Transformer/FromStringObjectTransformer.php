<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Transformer;

use Behat\Behat\Definition\Call\DefinitionCall;
use Closure;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Transforms typehinted parameters using their `fromString` factory method (if exists).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FromStringObjectTransformer implements ArgumentTransformer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return !is_object($argumentValue) && null !== $this->getParameterFactoryByIndex($definitionCall, $argumentIndex);
    }

    /**
     * {@inheritdoc}
     */
    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return $this->getParameterFactoryByIndex($definitionCall, $argumentIndex)->invoke(null, $argumentValue);
    }

    /**
     * Attempts to get definition parameter using its index (parameter position or name).
     *
     * @param DefinitionCall $definitionCall
     * @param string|integer $argumentIndex
     *
     * @return ReflectionMethod|null
     */
    private function getParameterFactoryByIndex(DefinitionCall $definitionCall, $argumentIndex)
    {
        $parameters = array_filter(
            array_filter($this->getCallParameters($definitionCall),
                $this->hasIndex($argumentIndex)
            ),
            $this->isClassWithMethod('fromString')
        );

        return count($parameters) ? current($parameters)->getClass()->getMethod('fromString') : null;
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
     * Returns closure to filter parameter by typehinted class and its method name.
     *
     * @param string $methodName
     *
     * @return Closure
     */
    private function isClassWithMethod($methodName)
    {
        return function (ReflectionParameter $parameter) use ($methodName) {
            return $parameter->getClass();
        };
    }
}
