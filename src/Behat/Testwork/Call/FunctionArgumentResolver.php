<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use Behat\Testwork\Call\Exception\UnknownParameterValueException;
use ReflectionFunctionAbstract as ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Resolves function arguments by reflection.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FunctionArgumentResolver
{
    /**
     * Resolves definition arguments.
     *
     * @param ReflectionFunction $function
     * @param string[]           $arguments
     * @param mixed[]            $extra
     *
     * @return mixed[]
     *
     * @throws UnknownParameterValueException
     */
    public function resolveArguments(
        ReflectionFunction $function,
        array $arguments,
        array $extra = null
    ) {
        $parameters = $function->getParameters();
        $arguments = $this->getMatchedArguments($function, $parameters, $arguments);

        if (null !== $extra) {
            $arguments = $this->appendExtraArguments($parameters, $arguments, $extra);
        }

        $this->validate($function, $parameters, $arguments);

        return $arguments;
    }

    /**
     * Returns array of matched arguments.
     *
     * @param ReflectionFunction    $function
     * @param ReflectionParameter[] $parameters
     * @param array                 $arguments
     *
     * @return mixed[]
     */
    private function getMatchedArguments(
        ReflectionFunction $function,
        array $parameters,
        array $arguments
    ) {
        $realArguments = array();

        $names = $this->getParameterNames($parameters);
        list($numArguments, $nameArguments) = $this->splitNumberedAndNamedArguments(
            $function,
            $arguments,
            $names
        );

        foreach ($parameters as $num => $parameter) {
            $name = $parameter->getName();

            if (isset($nameArguments[$name])) {
                $realArguments[$name] = $nameArguments[$name];
            } elseif (isset($numArguments[$num])) {
                $realArguments[$num] = $numArguments[$num];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $realArguments[$num] = $parameter->getDefaultValue();
            }
        }

        return $realArguments;
    }

    /**
     * Returns reflection parameter names.
     *
     * @param ReflectionParameter[] $parameters
     *
     * @return string[]
     */
    private function getParameterNames(array $parameters)
    {
        return array_map(
            function (ReflectionParameter $parameter) {
                return $parameter->getName();
            },
            $parameters
        );
    }

    /**
     * Appends multiline arguments to the end of the arguments list.
     *
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $arguments
     * @param mixed[]               $extra
     *
     * @return mixed[]
     */
    private function appendExtraArguments(array $parameters, array $arguments, array $extra)
    {
        foreach (array_values($extra) as $num => $argument) {
            $arguments[count($parameters) - 1 - $num] = $argument;
        }

        return $arguments;
    }

    /**
     * Splits matches into two separate arrays - numbered and named.
     *
     * `preg_match` matches named arguments with named indexes and also
     * represents all arguments with numbered indexes. This method splits
     * that one array into two independent ones.
     *
     * @param ReflectionFunction $function
     * @param array              $match
     * @param string[]           $parameterNames
     *
     * @return array
     *
     * @throws UnknownParameterValueException If some of the provided arguments are not expected
     */
    private function splitNumberedAndNamedArguments(
        ReflectionFunction $function,
        array $match,
        array $parameterNames
    ) {
        $numberedArguments = $match;
        $namedArguments = array();

        foreach ($match as $key => $val) {
            if (is_integer($key)) {
                continue;
            }

            if (!in_array($key, $parameterNames)) {
                throw new UnknownParameterValueException(sprintf(
                    '`%s` does not expect argument `$%s`.',
                    $this->getFunctionPath($function),
                    $key
                ));
            }

            $namedArguments[$key] = $val;
            unset($numberedArguments[$key]);
            unset($numberedArguments[array_search($val, $numberedArguments)]);
        }

        return array($numberedArguments, $namedArguments);
    }

    /**
     * Validates that all arguments are in place, throws exception otherwise.
     *
     * @param ReflectionFunction    $function
     * @param ReflectionParameter[] $parameters
     * @param mixed[]               $arguments
     *
     * @throws UnknownParameterValueException
     */
    private function validate(ReflectionFunction $function, array $parameters, array $arguments)
    {
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
     * @param ReflectionFunction $function
     *
     * @return string
     */
    private function getFunctionPath(ReflectionFunction $function)
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
}
