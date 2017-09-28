<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\HelperContainer\Exception\WrongServicesConfigurationException;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Behat helper container extension.
 *
 * Extends Behat with helper containers support.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HelperContainerExtension implements Extension
{
    /*
     * Available extension points
     */
    const HELPER_CONTAINER_TAG = 'helper_container.container';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes compiler pass.
     *
     * @param null|ServiceProcessor $processor
     */
    public function __construct(ServiceProcessor $processor = null)
    {
        $this->processor = $processor ? : new ServiceProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'helper_container';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('Behat\Behat\HelperContainer\Argument\ServicesResolverFactory', array($container));
        $definition->addTag(ContextExtension::SUITE_SCOPED_RESOLVER_FACTORY_TAG, array('priority' => 0));
        $container->setDefinition(ContextExtension::SUITE_SCOPED_RESOLVER_FACTORY_TAG . '.helper_container', $definition);

        $definition = new Definition('Behat\Behat\HelperContainer\Call\Filter\ServicesResolver');
        $definition->addTag(CallExtension::CALL_FILTER_TAG, array('priority' => 0));
        $container->setDefinition(CallExtension::CALL_FILTER_TAG . '.helper_container', $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::HELPER_CONTAINER_TAG);

        foreach ($references as $reference) {
            if ($this->isDefinitionShared($container->getDefinition((string) $reference))) {
                throw new WrongServicesConfigurationException(sprintf(
                    'Container services must not be configured as shared, but `@%s` is.', $reference
                ));
            }
        }
    }

    /**
     * Checks if provided definition is shared.
     *
     * @param Definition $definition
     *
     * @return bool
     *
     * @todo Remove after upgrading to Symfony 2.8+
     */
    private function isDefinitionShared(Definition $definition)
    {
        if (method_exists($definition, 'isShared')) {
            return $definition->isShared();
        } else if (method_exists($definition, 'getScope')) {
            return $definition->getScope() !== ContainerBuilder::SCOPE_PROTOTYPE;
        }

        return false;
    }
}
