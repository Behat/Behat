<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Call\Filter;

use Behat\Behat\Definition\Definition;
use Behat\Behat\HelperContainer\Environment\ServiceContainerEnvironment;
use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\HelperContainer\ArgumentAutowirer;
use Behat\Behat\HelperContainer\Exception\UnsupportedCallException;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Filter\CallFilter;
use Behat\Testwork\Environment\Call\EnvironmentCall;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Dynamically resolves call arguments using the service container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServicesResolver implements CallFilter
{
    /**
     * {@inheritdoc}
     */
    public function supportsCall(Call $call)
    {
        return ($call instanceof DefinitionCall || $call instanceof TransformationCall)
            && $call->getEnvironment() instanceof ServiceContainerEnvironment;
    }

    /**
     * Filters a call and returns a new one.
     *
     * @param Call $call
     *
     * @return Call
     *
     * @throws UnsupportedCallException
     * @throws ContainerExceptionInterface
     */
    public function filterCall(Call $call)
    {
        if ($container = $this->getContainer($call)) {
            $autowirer = new ArgumentAutowirer($container);
            $newArguments = $autowirer->autowireArguments($call->getCallee()->getReflection(), $call->getArguments());

            return $this->repackageCallIfNewArguments($call, $newArguments);
        }

        return $call;
    }

    /**
     * Gets container from the call.
     *
     * @param Call $call
     *
     * @return null|ContainerInterface
     *
     * @throws UnsupportedCallException if given call is not EnvironmentCall or environment is not ServiceContainerEnvironment
     */
    private function getContainer(Call $call)
    {
        if (!$call instanceof EnvironmentCall) {
            throw new UnsupportedCallException(sprintf(
                'ServicesResolver can not filter `%s` call.',
                get_class($call)
            ), $call);
        }

        $environment = $call->getEnvironment();

        if (!$environment instanceof ServiceContainerEnvironment) {
            throw new UnsupportedCallException(sprintf(
                'ServicesResolver can not filter `%s` call.',
                get_class($call)
            ), $call);
        }

        return $environment->getServiceContainer();
    }

    /**
     * Repackages old calls with new arguments, but only if two differ.
     *
     * @param Call $call
     * @param array $arguments
     *
     * @return Call
     *
     * @throws UnsupportedCallException if given call is not DefinitionCall or TransformationCall
     */
    private function repackageCallIfNewArguments(Call $call, array $arguments)
    {
        if ($arguments === $call->getArguments()) {
            return $call;
        }

        return $this->repackageCallWithNewArguments($call, $arguments);
    }

    /**
     * Repackages old calls with new arguments.
     *
     * @param Call  $call
     * @param array $newArguments
     *
     * @return DefinitionCall|TransformationCall
     *
     * @throws UnsupportedCallException
     */
    private function repackageCallWithNewArguments(Call $call, array $newArguments)
    {
        if ($call instanceof DefinitionCall) {
            return $this->repackageDefinitionCall($call, $newArguments);
        }

        if ($call instanceof TransformationCall) {
            return $this->repackageTransformationCall($call, $newArguments);
        }

        throw new UnsupportedCallException(
            sprintf(
                'ServicesResolver can not filter `%s` call.',
                get_class($call)
            ), $call
        );
    }

    /**
     * Repackages definition call with new arguments.
     *
     * @param DefinitionCall $call
     * @param array $newArguments
     *
     * @return DefinitionCall
     *
     * @throws UnsupportedCallException
     */
    private function repackageDefinitionCall(DefinitionCall $call, array $newArguments)
    {
        $definition = $call->getCallee();

        if (!$definition instanceof Definition) {
            throw new UnsupportedCallException(
                sprintf(
                    'Something is wrong in callee associated with `%s` call.',
                    get_class($call)
                ), $call
            );
        }

        return new DefinitionCall(
            $call->getEnvironment(),
            $call->getFeature(),
            $call->getStep(),
            $definition,
            $newArguments,
            $call->getErrorReportingLevel()
        );
    }

    /**
     * Repackages transformation call with new arguments.
     *
     * @param TransformationCall $call
     * @param array $newArguments
     *
     * @return TransformationCall
     *
     * @throws UnsupportedCallException
     */
    private function repackageTransformationCall(TransformationCall $call, array $newArguments)
    {
        $transformation = $call->getCallee();

        if (!$transformation instanceof Transformation) {
            throw new UnsupportedCallException(
                sprintf(
                    'Something is wrong in callee associated with `%s` call.',
                    get_class($call)
                ), $call
            );
        }

        return new TransformationCall(
            $call->getEnvironment(),
            $call->getDefinition(),
            $transformation,
            $newArguments
        );
    }
}
