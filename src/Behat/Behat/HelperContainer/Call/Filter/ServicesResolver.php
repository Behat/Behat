<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Call\Filter;

use Behat\Behat\HelperContainer\Environment\ServiceContainerEnvironment;
use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\HelperContainer\ArgumentAutowirer;
use Behat\Behat\HelperContainer\Exception\UnsupportedCallException;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Filter\CallFilter;
use Behat\Testwork\Environment\Call\EnvironmentCall;
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
     */
    public function filterCall(Call $call)
    {
        if ($container = $this->getContainer($call)) {
            $autowirer = new ArgumentAutowirer($container);
            $newArguments = $autowirer->autowireArguments($call->getCallee()->getReflection(), $call->getArguments());

            return $this->repackageCallWithNewArguments($call, $newArguments);
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
     * Repackages old calls with new arguments.
     *
     * @param Call $call
     * @param array $arguments
     *
     * @return Call
     *
     * @throws UnsupportedCallException if given call is not DefinitionCall or TransformationCall
     */
    private function repackageCallWithNewArguments(Call $call, array $arguments)
    {
        if ($arguments === $call->getArguments()) {
            return $call;
        }

        if ($call instanceof DefinitionCall) {
            return new DefinitionCall(
                $call->getEnvironment(),
                $call->getFeature(),
                $call->getStep(),
                $call->getCallee(),
                $arguments,
                $call->getErrorReportingLevel()
            );
        }

        if ($call instanceof TransformationCall) {
            return new TransformationCall(
                $call->getEnvironment(),
                $call->getDefinition(),
                $call->getCallee(),
                $arguments
            );
        }

        throw new UnsupportedCallException(sprintf(
            'ServicesResolver can not filter `%s` call.',
            get_class($call)
        ), $call);
    }
}
