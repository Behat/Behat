<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\ServiceContainer;

use Behat\Behat\Context\Annotation\DocBlockHelper;
use Behat\Behat\Context\Argument\CompositeArgumentResolverFactory;
use Behat\Behat\Context\Cli\ContextSnippetsController;
use Behat\Behat\Context\ContextClass\SimpleClassGenerator;
use Behat\Behat\Context\ContextFactory;
use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;
use Behat\Behat\Context\Environment\Reader\ContextEnvironmentReader;
use Behat\Behat\Context\Reader\AttributeContextReader;
use Behat\Behat\Context\Reader\ContextReaderCachedPerContext;
use Behat\Behat\Context\Reader\ContextReaderCachedPerSuite;
use Behat\Behat\Context\Reader\TranslatableContextReader;
use Behat\Behat\Context\Snippet\Appender\ContextSnippetAppender;
use Behat\Behat\Context\Snippet\Generator\ContextSnippetGenerator;
use Behat\Behat\Context\Suite\Setup\SuiteWithContextsSetup;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\Snippet\ServiceContainer\SnippetExtension;
use Behat\Testwork\Argument\ServiceContainer\ArgumentExtension;
use Behat\Testwork\Autoloader\ServiceContainer\AutoloaderExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\Filesystem\ServiceContainer\FilesystemExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat context extension.
 *
 * Extends Behat with context services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextExtension implements Extension
{
    /**
     * Available services.
     */
    public const FACTORY_ID = 'context.factory';
    public const CONTEXT_SNIPPET_GENERATOR_ID = 'snippet.generator.context';
    public const AGGREGATE_RESOLVER_FACTORY_ID = 'context.argument.aggregate_resolver_factory';
    private const ENVIRONMENT_HANDLER_ID = EnvironmentExtension::HANDLER_TAG . '.context';
    private const ENVIRONMENT_READER_ID = EnvironmentExtension::READER_TAG . '.context';
    private const SUITE_SETUP_ID = SuiteExtension::SETUP_TAG . '.suite_with_contexts';
    private const ATTRIBUTED_CONTEXT_READER_ID = self::READER_TAG . '.attributed';

    /*
     * Available extension points
     */
    public const CLASS_RESOLVER_TAG = 'context.class_resolver';
    public const ARGUMENT_RESOLVER_TAG = 'context.argument_resolver';
    public const INITIALIZER_TAG = 'context.initializer';
    public const READER_TAG = 'context.reader';
    public const ATTRIBUTE_READER_TAG = 'context.attribute_reader';
    public const CLASS_GENERATOR_TAG = 'context.class_generator';
    public const SUITE_SCOPED_RESOLVER_FACTORY_TAG = 'context.argument.suite_resolver_factory';
    public const DOC_BLOCK_HELPER_ID = 'context.docblock_helper';

    private readonly ServiceProcessor $processor;

    /**
     * Initializes compiler pass.
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function getConfigKey(): string
    {
        return 'contexts';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadFactory($container);
        $this->loadArgumentResolverFactory($container);
        $this->loadEnvironmentHandler($container);
        $this->loadEnvironmentReader($container);
        $this->loadSuiteSetup($container);
        $this->loadSnippetAppender($container);
        $this->loadSnippetGenerators($container);
        $this->loadSnippetsController($container);
        $this->loadDefaultClassGenerators($container);
        $this->loadDefaultContextReaders($container);
        $this->loadDocblockHelper($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processClassResolvers($container);
        $this->processArgumentResolverFactories($container);
        $this->processArgumentResolvers($container);
        $this->processContextInitializers($container);
        $this->processContextReaders($container);
        $this->processClassGenerators($container);
        $this->processAttributeReaders($container);
    }

    /**
     * Loads context factory.
     */
    private function loadFactory(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextFactory::class, [
            new Reference(ArgumentExtension::CONSTRUCTOR_ARGUMENT_ORGANISER_ID),
        ]);
        $container->setDefinition(self::FACTORY_ID, $definition);
    }

    /**
     * Loads argument resolver factory used in the environment handler.
     */
    private function loadArgumentResolverFactory(ContainerBuilder $container): void
    {
        $definition = new Definition(CompositeArgumentResolverFactory::class);
        $container->setDefinition(self::AGGREGATE_RESOLVER_FACTORY_ID, $definition);
    }

    /**
     * Loads context environment handlers.
     */
    private function loadEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextEnvironmentHandler::class, [
            new Reference(self::FACTORY_ID),
            new Reference(self::AGGREGATE_RESOLVER_FACTORY_ID),
        ]);
        $definition->addTag(EnvironmentExtension::HANDLER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ENVIRONMENT_HANDLER_ID, $definition);
    }

    /**
     * Loads context environment readers.
     */
    private function loadEnvironmentReader(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextEnvironmentReader::class);
        $definition->addTag(EnvironmentExtension::READER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ENVIRONMENT_READER_ID, $definition);
    }

    /**
     * Loads context environment setup.
     */
    private function loadSuiteSetup(ContainerBuilder $container): void
    {
        $definition = new Definition(SuiteWithContextsSetup::class, [
            new Reference(AutoloaderExtension::CLASS_LOADER_ID),
            new Reference(FilesystemExtension::LOGGER_ID),
        ]);
        $definition->addTag(SuiteExtension::SETUP_TAG, ['priority' => 20]);
        $container->setDefinition(self::SUITE_SETUP_ID, $definition);
    }

    /**
     * Loads context snippet appender.
     */
    private function loadSnippetAppender(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextSnippetAppender::class, [
            new Reference(FilesystemExtension::LOGGER_ID),
        ]);
        $definition->addTag(SnippetExtension::APPENDER_TAG, ['priority' => 50]);
        $container->setDefinition(SnippetExtension::APPENDER_TAG . '.context', $definition);
    }

    /**
     * Loads context snippet generators.
     */
    private function loadSnippetGenerators(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextSnippetGenerator::class, [
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
        ]);
        $definition->addTag(SnippetExtension::GENERATOR_TAG, ['priority' => 50]);
        $container->setDefinition(self::CONTEXT_SNIPPET_GENERATOR_ID, $definition);
    }

    private function loadSnippetsController(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextSnippetsController::class, [
            new Reference(self::CONTEXT_SNIPPET_GENERATOR_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 410]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.context_snippets', $definition);
    }

    /**
     * Loads default context class generators.
     */
    private function loadDefaultClassGenerators(ContainerBuilder $container): void
    {
        $definition = new Definition(SimpleClassGenerator::class);
        $definition->addTag(self::CLASS_GENERATOR_TAG, ['priority' => 50]);
        $container->setDefinition(self::CLASS_GENERATOR_TAG . '.simple', $definition);
    }

    /**
     * Loads default context readers.
     */
    private function loadDefaultContextReaders(ContainerBuilder $container): void
    {
        $this->loadAttributedContextReader($container);

        $this->loadTranslatableContextReader($container);
    }

    /**
     * Loads AttributedContextReader.
     */
    private function loadAttributedContextReader(ContainerBuilder $container): Definition
    {
        $definition = new Definition(AttributeContextReader::class);
        $container->setDefinition(self::ATTRIBUTED_CONTEXT_READER_ID, $definition);

        $definition = new Definition(ContextReaderCachedPerContext::class, [
            new Reference(self::ATTRIBUTED_CONTEXT_READER_ID),
        ]);
        $definition->addTag(self::READER_TAG, ['priority' => 50]);
        $container->setDefinition(self::ATTRIBUTED_CONTEXT_READER_ID . '.cached', $definition);

        return $definition;
    }

    /**
     * Loads TranslatableContextReader.
     */
    private function loadTranslatableContextReader(ContainerBuilder $container): void
    {
        $definition = new Definition(TranslatableContextReader::class, [
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $container->setDefinition(self::READER_TAG . '.translatable', $definition);

        $definition = new Definition(ContextReaderCachedPerSuite::class, [
            new Reference(self::READER_TAG . '.translatable'),
        ]);
        $definition->addTag(self::READER_TAG, ['priority' => 50]);
        $container->setDefinition(self::READER_TAG . '.translatable.cached', $definition);
    }

    /**
     * Loads DocBlockHelper.
     */
    private function loadDocblockHelper(ContainerBuilder $container): void
    {
        $definition = new Definition(DocBlockHelper::class);

        $container->setDefinition(self::DOC_BLOCK_HELPER_ID, $definition);
    }

    /**
     * Processes all class resolvers.
     */
    private function processClassResolvers(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::CLASS_RESOLVER_TAG);
        $definition = $container->getDefinition(self::ENVIRONMENT_HANDLER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerClassResolver', [$reference]);
        }
    }

    /**
     * Processes all argument resolver factories.
     *
     * @param ContainerBuilder $container
     */
    private function processArgumentResolverFactories($container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::SUITE_SCOPED_RESOLVER_FACTORY_TAG);
        $definition = $container->getDefinition(self::AGGREGATE_RESOLVER_FACTORY_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerFactory', [$reference]);
        }
    }

    /**
     * Processes all argument resolvers.
     */
    private function processArgumentResolvers(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::ARGUMENT_RESOLVER_TAG);
        $definition = $container->getDefinition(self::FACTORY_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerArgumentResolver', [$reference]);
        }
    }

    /**
     * Processes all context initializers.
     */
    private function processContextInitializers(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::INITIALIZER_TAG);
        $definition = $container->getDefinition(self::FACTORY_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerContextInitializer', [$reference]);
        }
    }

    /**
     * Processes all context readers.
     */
    private function processContextReaders(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::READER_TAG);
        $definition = $container->getDefinition(self::ENVIRONMENT_READER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerContextReader', [$reference]);
        }
    }

    /**
     * Processes all class generators.
     */
    private function processClassGenerators(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::CLASS_GENERATOR_TAG);
        $definition = $container->getDefinition(self::SUITE_SETUP_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerClassGenerator', [$reference]);
        }
    }

    /**
     * Processes all attribute readers.
     */
    private function processAttributeReaders(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::ATTRIBUTE_READER_TAG);
        $definition = $container->getDefinition(self::ATTRIBUTED_CONTEXT_READER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerAttributeReader', [$reference]);
        }
    }
}
