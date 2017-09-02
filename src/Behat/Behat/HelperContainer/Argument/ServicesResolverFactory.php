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
use Behat\Behat\HelperContainer\Environment\ServiceContainerEnvironment;
use Behat\Behat\Context\Argument\ArgumentResolverFactory;
use Behat\Behat\Context\Argument\SuiteScopedResolverFactory;
use Behat\Behat\HelperContainer\BuiltInServiceContainer;
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
     * @var TaggedContainerInterface
     */
    private $container;

    /**
     * Initialises factory.
     *
     * @param TaggedContainerInterface $container
     */
    public function __construct(TaggedContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
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
            return array();
        }

        $container = $this->createContainer($suite->getSetting('services'));

        return $this->createResolvers($container, false);
    }

    /**
     * {@inheritdoc}
     *
     * @throws WrongServicesConfigurationException
     * @throws WrongContainerClassException
     */
    public function createArgumentResolvers(Environment $environment)
    {
        $suite = $environment->getSuite();

        if (!$suite->hasSetting('services')) {
            return array();
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
     * @param string $settings
     *
     * @return mixed
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
     * @return mixed
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
     *
     * @param array $settings
     *
     * @return BuiltInServiceContainer
     */
    private function createContainerFromArray(array $settings)
    {
        return new BuiltInServiceContainer($settings);
    }

    /**
     * Loads container from string.
     *
     * @param string $name
     *
     * @return mixed
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
     *
     * @return mixed
     */
    private function createContainerFromClassSpec($classSpec)
    {
        $constructor = explode('::', $classSpec);

        if (2 === count($constructor)) {
            return call_user_func($constructor);
        }

        return new $constructor[0];
    }

    /**
     * Checks if container implements the correct interface and creates resolver using it.
     *
     * @param mixed $container
     * @param bool  $autowire
     *
     * @return ArgumentResolver[]
     *
     * @throws WrongContainerClassException
     */
    private function createResolvers($container, $autowire)
    {
        if (!$container instanceof ContainerInterface) {
            throw new WrongContainerClassException(
                sprintf(
                    'Service container is expected to implement `Psr\Container\ContainerInterface`, but `%s` does not.',
                    get_class($container)
                ),
                get_class($container)
            );
        }

        if ($autowire) {
            return array(new ServicesResolver($container), new AutowiringResolver($container));
        }

        return array(new ServicesResolver($container));
    }
}
