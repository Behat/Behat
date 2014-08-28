<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

use Behat\Testwork\Argument\Exception\UnknownParameterValueException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Organises function arguments using its reflection.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class MixedArgumentOrganiser implements ArgumentOrganiser
{
    private $definedArguments = array();

    /**
     * Organises arguments using function reflection.
     *
     * @param ReflectionFunctionAbstract $function
     * @param mixed[]                    $arguments
     *
     * @return mixed[]
     */
    public function organiseArguments(ReflectionFunctionAbstract $function, array $arguments)
    {
        $parameters = $function->getParameters();
        $arguments = $this->prepareArguments($parameters, $arguments);

        $this->validateArguments($function, $parameters, $arguments);

        return $arguments;
    }

    /**
     * Prepares arguments based on provided parameters.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $arguments
     *
     * @return mixed[]
     */
    private function prepareArguments(array $parameters, array $arguments)
    {
        $this->markAllArgumentsUndefined();

        list($named, $typehinted, $numbered) = $this->splitArguments($parameters, $arguments);

        $arguments =
            $this->prepareNamedArguments($parameters, $named) +
            $typehinted +
            $this->prepareNumberedArguments($parameters, $numbered) +
            $this->prepareDefaultArguments($parameters);

        return $this->reorderArguments($parameters, $arguments);
    }

    /**
     * Splits arguments into three separate arrays - named, numbered and typehinted.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $arguments
     *
     * @return array
     */
    private function splitArguments(array $parameters, array $arguments)
    {
        $parameterNames = array_map(
            function (ReflectionParameter $parameter) {
                return $parameter->getName();
            },
            $parameters
        );

        $namedArguments = array();
        $numberedArguments = array();
        $typehintedArguments = array();
        foreach ($arguments as $key => $val) {
            if ($this->isStringKeyAndExistsInParameters($key, $parameterNames)) {
                $namedArguments[$key] = $val;
            } elseif ($num = $this->getParameterNumberWithTypehintingValue($parameters, $val)) {
                $typehintedArguments[$num] = $val;
            } else {
                $numberedArguments[] = $val;
            }
        }

        return array($namedArguments, $typehintedArguments, $numberedArguments);
    }

    /**
     * Checks that provided argument key is a string and it matches some parameter name.
     *
     * @param mixed    $argumentKey
     * @param string[] $parameterNames
     *
     * @return Boolean
     */
    private function isStringKeyAndExistsInParameters($argumentKey, $parameterNames)
    {
        return is_string($argumentKey) && in_array($argumentKey, $parameterNames);
    }

    /**
     * Tries to find a parameter number, which typehints provided value.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed                 $value
     *
     * @return null|integer
     */
    private function getParameterNumberWithTypehintingValue(array $parameters, $value)
    {
        if (!is_object($value)) {
            return null;
        }

        foreach ($parameters as $num => $parameter) {
            if ($this->isValueMatchesTypehintedParameter($value, $parameter)) {
                return $num;
            }
        }

        return null;
    }

    /**
     * Checks if value matches typehint of provided parameter.
     *
     * @param object              $value
     * @param ReflectionParameter $parameter
     *
     * @return Boolean
     */
    private function isValueMatchesTypehintedParameter($value, ReflectionParameter $parameter)
    {
        $typehintRefl = $parameter->getClass();

        return $typehintRefl && $typehintRefl->isInstance($value);
    }

    /**
     * Captures argument values based on their respective names.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $namedArguments
     *
     * @return mixed[]
     */
    private function prepareNamedArguments(array $parameters, array $namedArguments)
    {
        $arguments = array();

        foreach ($parameters as $num => $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $namedArguments)) {
                $arguments[$name] = $namedArguments[$name];
                $this->markArgumentDefined($num);
            }
        }

        return $arguments;
    }

    /**
     * Captures argument values for undefined arguments based on their respective numbers.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $numberedArguments
     *
     * @return mixed[]
     */
    private function prepareNumberedArguments(array $parameters, array $numberedArguments)
    {
        $arguments = array();

        $increment = 0;
        foreach ($parameters as $num => $parameter) {
            if ($this->isArgumentDefined($num)) {
                continue;
            }

            if (array_key_exists($increment, $numberedArguments)) {
                $arguments[$num] = $numberedArguments[$increment++];
                $this->markArgumentDefined($num);
            }
        }

        return $arguments;
    }

    /**
     * Captures argument values for undefined arguments based on parameters defaults.
     *
     * @param ReflectionParameter[] $parameters
     *
     * @return mixed[]
     */
    private function prepareDefaultArguments(array $parameters)
    {
        $arguments = array();

        foreach ($parameters as $num => $parameter) {
            if ($this->isArgumentDefined($num)) {
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[$num] = $parameter->getDefaultValue();
                $this->markArgumentDefined($num);
            }
        }

        return $arguments;
    }

    /**
     * Reorders arguments based on their respective parameters order.
     *
     * @param ReflectionParameter[] $parameters
     * @param array                 $arguments
     *
     * @return mixed[]
     */
    private function reorderArguments(array $parameters, array $arguments)
    {
        $orderedArguments = array();

        foreach ($parameters as $num => $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($num, $arguments)) {
                $orderedArguments[$num] = $arguments[$num];
            } elseif (array_key_exists($name, $arguments)) {
                $orderedArguments[$name] = $arguments[$name];
            }
        }

        return $orderedArguments;
    }

    /**
     * Validates that all arguments are in place, throws exception otherwise.
     *
     * @param ReflectionFunctionAbstract $function
     * @param ReflectionParameter[]      $parameters
     * @param mixed[]                    $arguments
     *
     * @throws UnknownParameterValueException
     */
    private function validateArguments(
        ReflectionFunctionAbstract $function,
        array $parameters,
        array $arguments
    ) {
        foreach ($parameters as $num => $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($num, $arguments) || array_key_exists($name, $arguments)) {
                continue;
            }

            throw new UnknownParameterValueException(sprintf(
                'Can not find a matching value for an argument `$%s` of the method `%s`.',
                $name,
                $this->getFunctionPath($function)
            ));
        }
    }

    /**
     * Returns function path for a provided reflection.
     *
     * @param ReflectionFunctionAbstract $function
     *
     * @return string
     */
    private function getFunctionPath(ReflectionFunctionAbstract $function)
    {
        if ($function instanceof ReflectionMethod) {
            return sprintf(
                '%s::%s()',
                $function->getDeclaringClass()->getName(),
                $function->getName()
            );
        }

        return sprintf('%s()', $function->getName());
    }

    /**
     * Marks arguments at all positions as undefined.
     *
     * This is used to share state between get*Arguments() methods.
     */
    private function markAllArgumentsUndefined()
    {
        $this->definedArguments = array();
    }

    /**
     * Marks an argument at provided position as defined.
     *
     * @param integer $position
     */
    private function markArgumentDefined($position)
    {
        $this->definedArguments[$position] = true;
    }

    /**
     * Checks if an argument at provided position is defined.
     *
     * @param integer $position
     *
     * @return Boolean
     */
    private function isArgumentDefined($position)
    {
        return isset($this->definedArguments[$position]);
    }
}
