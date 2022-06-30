<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

use ReflectionFunctionAbstract;
use ReflectionClass;
use ReflectionParameter;
use ReflectionException;

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
        return $this->prepareArguments($function->getParameters(), $arguments);
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
            $this->prepareTypehintedArguments($parameters, $typehinted) +
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
            } elseif ($this->isParameterTypehintedInArgumentList($parameters, $val)) {
                $typehintedArguments[] = $val;
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
     * @return bool
     */
    private function isStringKeyAndExistsInParameters($argumentKey, $parameterNames)
    {
        return is_string($argumentKey) && in_array($argumentKey, $parameterNames);
    }

    /**
     * Check if a given value is typehinted in the argument list.
     *
     * @param  ReflectionParameter[] $parameters
     * @param  mixed                 $value
     *
     * @return bool
     */
    private function isParameterTypehintedInArgumentList(array $parameters, $value)
    {
        if (!is_object($value)) {
            return false;
        }

        foreach ($parameters as $parameter) {
            if ($this->isValueMatchesTypehintedParameter($value, $parameter)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if value matches typehint of provided parameter.
     */
    private function isValueMatchesTypehintedParameter($value, ReflectionParameter $parameter) : bool
    {
        foreach($this->getReflectionClassesFromParameter($parameter) as $typehintRefl) {
            if($typehintRefl->isInstance($value)) {
                return true;
            }
        }

        return false;
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
     * Captures argument values for typehinted arguments based on the given candidates.
     *
     * This method attempts to match up the best fitting arguments to each constructor argument.
     *
     * This case specifically fixes the issue where a constructor asks for a parent and child class,
     * as separate arguments, but both arguments could satisfy the first argument,
     * so they would both be passed in (overwriting each other).
     *
     * This will ensure that the children (exact class matches) are mapped first, and then other dependencies
     * are mapped sequentially (to arguments which they are an `instanceof`).
     *
     * As such, this requires two passes of the $parameters array to ensure it is mapped as accurately as possible.
     *
     * @param ReflectionParameter[] $parameters          Reflection Parameters (constructor argument requirements)
     * @param mixed[]               $typehintedArguments Resolved arguments
     *
     * @return mixed[] Ordered list of arguments, index is the constructor argument position, value is what will be injected
     */
    private function prepareTypehintedArguments(array $parameters, array $typehintedArguments)
    {
        $arguments = array();

        $candidates = $typehintedArguments;

        $this->applyPredicateToTypehintedArguments(
            $parameters,
            $candidates,
            $arguments,
            array($this, 'classMatchingPredicateForTypehintedArguments')
        );

        // This iteration maps up everything else, providing the argument is an instanceof the parameter.
        $this->applyPredicateToTypehintedArguments(
            $parameters,
            $candidates,
            $arguments,
            array($this, 'isInstancePredicateForTypehintedArguments')
        );

        return $arguments;
    }

    /**
     * Filtered out superfluous parameters for matching up typehinted arguments.
     *
     * @param  ReflectionParameter[] $parameters Constructor Arguments
     * @return ReflectionParameter[]             Filtered $parameters
     */
    private function filterApplicableTypehintedParameters(array $parameters) : array
    {
        return array_filter($parameters,
            function($parameter, $num) {
                return !$this->isArgumentDefined($num)
                && $this->getReflectionClassesFromParameter($parameter);
            },
            ARRAY_FILTER_USE_BOTH
        );

    }

    /**
     * @return ReflectionClass[]
     */
    private function getReflectionClassesFromParameter(\ReflectionParameter $parameter): array
    {
        $classes = [];

        if (!$parameter->hasType()) {
            return $classes;
        }

        $type = $parameter->getType();

        /**
         * @psalm-suppress UndefinedClass (ReflectionUnionType)
         */
        if ($type instanceof \ReflectionNamedType) {
            $types = [$type];
        }
        elseif ($parameter->getType() instanceof \ReflectionUnionType) {
            $types = $type->getTypes();
        }
        else {
            $types = [];
        }

        foreach ($types as $type) {

            // ReflectionUnionType::getTypes is only documented as returning ReflectionType[]
            if (!$type instanceof \ReflectionNamedType) {
                continue;
            }

            $typeString = $type->getName();

            if ($typeString == 'self') {
                $typeString = $parameter->getDeclaringClass();
            } elseif ($typeString == 'parent') {
                $typeString = $parameter->getDeclaringClass()->getParentClass();
            }

            try {
                $classes[] = new ReflectionClass($typeString);
            } catch (ReflectionException $e) {
                continue;
            }
        }

        return $classes;
    }

    /**
     * Applies a predicate for each candidate when matching up typehinted arguments.
     * This passes through to another loop of the candidates in @matchParameterToCandidateUsingPredicate,
     * because this method is "too complex" with two loops...
     *
     * @param  ReflectionParameter[] $parameters Reflection Parameters (constructor argument requirements)
     * @param  mixed[]               &$candidates Resolved arguments
     * @param  mixed[]               &$arguments  Argument mapping
     * @param  callable              $predicate   Callable predicate to apply to each candidate
     * @return void
     */
    private function applyPredicateToTypehintedArguments(
        array $parameters,
        array &$candidates,
        array &$arguments,
        $predicate
    ) {
        $filtered = $this->filterApplicableTypehintedParameters($parameters);

        foreach ($filtered as $num => $parameter) {
            $this->matchParameterToCandidateUsingPredicate($parameter, $candidates, $arguments, $predicate);
        }
    }

    /**
     * Applies a predicate for each candidate when matching up typehinted arguments.
     * This helps to avoid repetition when looping them, as multiple passes are needed over the parameters / candidates.
     *
     * @param  ReflectionParameter $parameter   Reflection Parameter (constructor argument requirements)
     * @param  mixed[]             &$candidates Resolved arguments
     * @param  mixed[]             &$arguments  Argument mapping
     * @param  callable            $predicate   Callable predicate to apply to each candidate
     * @return bool Returns true if a candidate has been matched to the given parameter, otherwise false
     */
    public function matchParameterToCandidateUsingPredicate(
        ReflectionParameter $parameter,
        array &$candidates,
        array &$arguments,
        $predicate
    ) {
        foreach ($candidates as $candidateIndex => $candidate) {
            foreach($this->getReflectionClassesFromParameter($parameter) as $class) {
                if ($predicate($class, $candidate)) {
                    $num = $parameter->getPosition();

                    $arguments[$num] = $candidate;

                    $this->markArgumentDefined($num);

                    unset($candidates[$candidateIndex]);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Typehinted argument predicate to check if the argument and parameter classes match equally.
     *
     * @param  ReflectionClass $reflectionClass Typehinted argument
     * @param  mixed           $candidate       Resolved argument
     * @return bool
     */
    private function classMatchingPredicateForTypehintedArguments(ReflectionClass $reflectionClass, $candidate)
    {
        return $reflectionClass->getName() === get_class($candidate);
    }

    /**
     * Typehinted argument predicate to check if the argument is an instance of the parameter.
     *
     * @param  ReflectionClass $reflectionClass Typehinted argument
     * @param  mixed           $candidate       Resolved argument
     * @return bool
     */
    private function isInstancePredicateForTypehintedArguments(ReflectionClass $reflectionClass, $candidate)
    {
        return $reflectionClass->isInstance($candidate);
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
     * @return bool
     */
    private function isArgumentDefined($position)
    {
        return isset($this->definedArguments[$position]);
    }
}
