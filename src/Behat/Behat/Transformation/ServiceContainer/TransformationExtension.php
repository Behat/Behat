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
use Behat\Behat\Transformation\Call\Filter\DefinitionArgumentsTransformer;
use Behat\Behat\Transformation\Context\Attribute\TransformationAttributeReader;
use Behat\Behat\Transformation\TransformationRepository;
use Behat\Behat\Transformation\Transformer\RepositoryArgumentTransformer;
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
    public const REPOSITORY_ID = 'transformation.repository';

    /*
     * Available extension points
     */
    public const ARGUMENT_TRANSFORMER_TAG = 'transformation.argument_transformer';

    protected const DEFINITION_ARGUMENT_TRANSFORMER_ID = CallExtension::CALL_FILTER_TAG . '.definition_argument_transformer';

    private readonly ServiceProcessor $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function getConfigKey()
    {
        return 'transformations';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadDefinitionArgumentsTransformer($container);
        $this->loadDefaultTransformers($container);
        $this->loadAttributeReader($container);
        $this->loadRepository($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processArgumentsTransformers($container);
    }

    /**
     * Loads definition arguments transformer.
     */
    protected function loadDefinitionArgumentsTransformer(ContainerBuilder $container)
    {
        $definition = new Definition(DefinitionArgumentsTransformer::class);
        $definition->addTag(CallExtension::CALL_FILTER_TAG, ['priority' => 200]);
        $container->setDefinition(self::DEFINITION_ARGUMENT_TRANSFORMER_ID, $definition);
    }

    /**
     * Loads default transformers.
     */
    protected function loadDefaultTransformers(ContainerBuilder $container)
    {
        $definition = new Definition(RepositoryArgumentTransformer::class, [
            new Reference(self::REPOSITORY_ID),
            new Reference(CallExtension::CALL_CENTER_ID),
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $definition->addTag(self::ARGUMENT_TRANSFORMER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ARGUMENT_TRANSFORMER_TAG . '.repository', $definition);
    }

    /**
     * Loads transformation attribute reader.
     */
    private function loadAttributeReader(ContainerBuilder $container): void
    {
        $definition = new Definition(TransformationAttributeReader::class, [
            new Reference(DefinitionExtension::DOC_BLOCK_HELPER_ID),
        ]);
        $definition->addTag(ContextExtension::ATTRIBUTE_READER_TAG, ['priority' => 50]);
        $container->setDefinition(ContextExtension::ATTRIBUTE_READER_TAG . '.transformation', $definition);
    }

    /**
     * Loads transformations repository.
     */
    protected function loadRepository(ContainerBuilder $container)
    {
        $definition = new Definition(TransformationRepository::class, [
            new Reference(EnvironmentExtension::MANAGER_ID),
        ]);
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Processes all available argument transformers.
     */
    protected function processArgumentsTransformers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::ARGUMENT_TRANSFORMER_TAG);
        $definition = $container->getDefinition(self::DEFINITION_ARGUMENT_TRANSFORMER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerArgumentTransformer', [$reference]);
        }
    }

    /**
     * Returns definition argument transformer service id.
     *
     * @return string
     *
     * @deprecated Use DEFINITION_ARGUMENT_TRANSFORMER_ID constant instead
     *
     * @todo Remove method in next major version
     */
    protected function getDefinitionArgumentTransformerId()
    {
        return self::DEFINITION_ARGUMENT_TRANSFORMER_ID;
    }
}
