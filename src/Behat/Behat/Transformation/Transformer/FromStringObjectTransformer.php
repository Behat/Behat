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
use Behat\Behat\Transformation\Exception\FactoryMethodNotFound;
use Closure;
use ReflectionClass;
use ReflectionParameter;

/**
 * Transforms typehinted parameters using their `fromString` factory method (if exists).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FromStringObjectTransformer implements ArgumentTransformer
{
    const FACTORY_METHOD = 'fromString';

    /**
     * {@inheritdoc}
     */
    public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        return !is_object($argumentValue) && null !== $this->getParameterClassByIndex($definitionCall, $argumentIndex);
    }

    /**
     * {@inheritdoc}
     */
    public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue)
    {
        $class = $this->getParameterClassByIndex($definitionCall, $argumentIndex);
        $factoryMethod = self::FACTORY_METHOD;

        if (!$class->hasMethod($factoryMethod) || 1 !== $class->getMethod($factoryMethod)->getNumberOfParameters()) {
            $definitionPath = $definitionCall->getCallee()->getPath();
            $className = $class->getName();
            $argumentIndicator = is_string($argumentIndex) ? "`\$$argumentIndex`" : ($argumentIndex + 1);

            throw new FactoryMethodNotFound(
                "Argument $argumentIndicator of `$definitionPath` was type-hinted as `$className`, " .
                "but `$className::$factoryMethod(\$string)` is not implemented.\n" .
                "Either implement `$className::$factoryMethod(\$string)` method or define your own custom " .
                "transformation for the argument.",
                $className,
                $factoryMethod
            );
        }

        return $class->getMethod($factoryMethod)->invoke(null, $argumentValue);
    }

    /**
     * Attempts to get definition parameter using its index (parameter position or name).
     *
     * @param DefinitionCall $definitionCall
     * @param string|integer $argumentIndex
     *
     * @return ReflectionClass|null
     */
    private function getParameterClassByIndex(DefinitionCall $definitionCall, $argumentIndex)
    {
        $parameters = array_filter(
            array_filter($this->getCallParameters($definitionCall),
                $this->hasIndex($argumentIndex)
            ),
            $this->isClass()
        );

        return count($parameters) ? current($parameters)->getClass() : null;
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
    private function isClass()
    {
        return function (ReflectionParameter $parameter) {
            return $parameter->getClass();
        };
    }
}
