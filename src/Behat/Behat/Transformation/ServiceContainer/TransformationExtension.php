<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides definition arguments transformation functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TransformationExtension implements Extension
{
    /*
     * Available services
     */
    const REPOSITORY_ID = 'transformation.repository';

    /*
     * Available extension points
     */
    const ARGUMENT_TRANSFORMER_TAG = 'transformation.argument_transformer';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     *
     * @param null|ServiceProcessor $processor
     */
    public function __construct(ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'transformations';
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
        $this->loadDefinitionArgumentsTransformer($container);
        $this->loadDefaultTransformers($container);
        $this->loadAnnotationReader($container);
        $this->loadRepository($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processArgumentsTransformers($container);
    }

    /**
     * Loads definition arguments transformer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadDefinitionArgumentsTransformer(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Transformation\Call\Filter\DefinitionArgumentsTransformer');
        $definition->addTag(CallExtension::CALL_FILTER_TAG, array('priority' => 200));
        $container->setDefinition($this->getDefinitionArgumentTransformerId(), $definition);
    }

    /**
     * Loads default transformers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadDefaultTransformers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Transformation\Transformer\RepositoryArgumentTransformer', array(
            new Reference(self::REPOSITORY_ID),
            new Reference(CallExtension::CALL_CENTER_ID),
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID)
        ));
        $definition->addTag(self::ARGUMENT_TRANSFORMER_TAG, array('priority' => 50));
        $container->setDefinition(self::ARGUMENT_TRANSFORMER_TAG . '.repository', $definition);
    }

    /**
     * Loads transformation context annotation reader.
     *
     * @param ContainerBuilder $container
     */
    protected function loadAnnotationReader(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Transformation\Context\Annotation\TransformationAnnotationReader');
        $definition->addTag(ContextExtension::ANNOTATION_READER_TAG, array('priority' => 50));
        $container->setDefinition(ContextExtension::ANNOTATION_READER_TAG . '.transformation', $definition);
    }

    /**
     * Loads transformations repository.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRepository(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Transformation\TransformationRepository', array(
            new Reference(EnvironmentExtension::MANAGER_ID)
        ));
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Processes all available argument transformers.
     *
     * @param ContainerBuilder $container
     */
    protected function processArgumentsTransformers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::ARGUMENT_TRANSFORMER_TAG);
        $definition = $container->getDefinition($this->getDefinitionArgumentTransformerId());

        foreach ($references as $reference) {
            $definition->addMethodCall('registerArgumentTransformer', array($reference));
        }
    }

    /**
     * Returns definition argument transformer service id.
     *
     * @return string
     */
    protected function getDefinitionArgumentTransformerId()
    {
        return CallExtension::CALL_FILTER_TAG . '.definition_argument_transformer';
    }
}
