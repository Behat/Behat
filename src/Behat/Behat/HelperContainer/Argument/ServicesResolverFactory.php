<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Argument;

use Behat\Behat\Context\Argument\ArgumentResolver;
use Behat\Behat\Context\Argument\ArgumentResolverFactory;
use Behat\Behat\Context\Argument\SuiteScopedResolverFactory;
use Behat\Behat\HelperContainer\BuiltInServiceContainer;
use Behat\Behat\HelperContainer\Environment\ServiceContainerEnvironment;
use Behat\Behat\HelperContainer\Exception\WrongContainerClassException;
use Behat\Behat\HelperContainer\Exception\WrongServicesConfigurationException;
use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

/**
 * Generates ServiceContainer argument resolvers based on suite's `services` setting.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServicesResolverFactory implements SuiteScopedResolverFactory, ArgumentResolverFactory
{
    /**
     * Initialises factory.
     */
    public function __construct(
        private readonly TaggedContainerInterface $container,
    ) {
    }

    /**
     * @deprecated as part of SuiteScopedResolverFactory deprecation. Would be removed in 4.0
     *
     * @throws WrongServicesConfigurationException
     * @throws WrongContainerClassException
     */
    public function generateArgumentResolvers(Suite $suite)
    {
        @trigger_error(
            'SuiteScopedResolverFactory::generateArgumentResolvers() was deprecated and will be removed in 4.0',
            E_USER_DEPRECATED
        );

        if (!$suite->hasSetting('services')) {
            return [];
        }

        $container = $this->createContainer($suite->getSetting('services'));

        return $this->createResolvers($container, false);
    }

    /**
     * @throws WrongServicesConfigurationException
     * @throws WrongContainerClassException
     */
    public function createArgumentResolvers(Environment $environment)
    {
        $suite = $environment->getSuite();

        if (!$suite->hasSetting('services')) {
            return [];
        }

        $container = $this->createContainer($suite->getSetting('services'));
        $autowire = $suite->hasSetting('autowire') && $suite->getSetting('autowire');

        if ($environment instanceof ServiceContainerEnvironment) {
            $environment->setServiceContainer($container);
        }

        return $this->createResolvers($container, $autowire);
    }

    /**
     * Creates container from the setting passed.
     *
     * @throws WrongServicesConfigurationException
     */
    private function createContainer($settings)
    {
        if (is_string($settings)) {
            return $this->createContainerFromString($settings);
        }

        if (is_array($settings)) {
            return $this->createContainerFromArray($settings);
        }

        throw new WrongServicesConfigurationException(
            sprintf('`services` must be either string or an array, but `%s` given.', gettype($settings))
        );
    }

    /**
     * Creates custom container using class/constructor given.
     *
     * @param string $settings
     *
     * @throws WrongServicesConfigurationException
     */
    private function createContainerFromString($settings)
    {
        if (0 === mb_strpos($settings, '@')) {
            return $this->loadContainerFromContainer(mb_substr($settings, 1));
        }

        return $this->createContainerFromClassSpec($settings);
    }

    /**
     * Creates built-in service container with provided settings.
     */
    private function createContainerFromArray(array $settings): BuiltInServiceContainer
    {
        return new BuiltInServiceContainer($settings);
    }

    /**
     * Loads container from string.
     *
     * @param string $name
     *
     * @throws WrongServicesConfigurationException
     */
    private function loadContainerFromContainer($name)
    {
        $services = $this->container->findTaggedServiceIds(HelperContainerExtension::HELPER_CONTAINER_TAG);

        if (!array_key_exists($name, $services)) {
            throw new WrongServicesConfigurationException(
                sprintf('Service container `@%s` was not found.', $name)
            );
        }

        return $this->container->get($name);
    }

    /**
     * Creates container from string-based class spec.
     *
     * @param string $classSpec
     */
    private function createContainerFromClassSpec($classSpec)
    {
        $constructor = explode('::', $classSpec);

        if (2 === count($constructor)) {
            return call_user_func($constructor);
        }

        return new $constructor[0]();
    }

    /**
     * Checks if container implements the correct interface and creates resolver using it.
     *
     * @param bool  $autowire
     *
     * @return list<ArgumentResolver>
     *
     * @throws WrongContainerClassException
     */
    private function createResolvers($container, $autowire): array
    {
        if (!$container instanceof ContainerInterface) {
            throw new WrongContainerClassException(
                sprintf(
                    'Service container is expected to implement `Psr\Container\ContainerInterface`, but `%s` does not.',
                    $container::class
                ),
                $container::class
            );
        }

        if ($autowire) {
            return [new ServicesResolver($container), new AutowiringResolver($container)];
        }

        return [new ServicesResolver($container)];
    }
}
