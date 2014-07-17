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

        list($numberedArgs, $namedArgs) = $this->splitArguments($parameters, $arguments);

        $arguments =
            $this->getNamedArguments($parameters, $namedArgs) +
            $this->getNumberedArguments($parameters, $numberedArgs) +
            $this->getDefaultArguments($parameters);

        return $this->reorderArguments($parameters, $arguments);
    }

    /**
     * Splits arguments into two separate arrays - numbered and named.
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
        foreach ($arguments as $key => $val) {
            if (is_string($key) && in_array($key, $parameterNames)) {
                $namedArguments[$key] = $val;
            } else {
                $numberedArguments[] = $val;
            }
        }

        return array($numberedArguments, $namedArguments);
    }

    /**
     * Captures argument values based on their respective names.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $namedArguments
     *
     * @return mixed[]
     */
    private function getNamedArguments(array $parameters, array $namedArguments)
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
    private function getNumberedArguments(array $parameters, array $numberedArguments)
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
    private function getDefaultArguments(array $parameters)
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
