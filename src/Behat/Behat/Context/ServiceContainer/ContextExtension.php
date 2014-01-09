<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\ServiceContainer;

use Behat\Behat\Snippet\ServiceContainer\SnippetExtension;
use Behat\Testwork\Autoloader\ServiceContainer\AutoloaderExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\Filesystem\ServiceContainer\FilesystemExtension;
use Behat\Testwork\ServiceContainer\Extension;
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
class ContextExtension implements Extension
{
    /*
     * Available extension points
     */
    const INITIALIZER_TAG = 'context.initializer';
    const ARGUMENT_RESOLVER_TAG = 'context.argument_resolver';
    const READER_TAG = 'context.reader';
    const CLASS_GENERATOR_TAG = 'context.class_generator';
    const ANNOTATION_READER_TAG = 'context.annotation_reader';

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
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'context';
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadEnvironmentHandlers($container);
        $this->loadEnvironmentReader($container);
        $this->loadSuiteSetup($container);
        $this->loadSnippetAppender($container);
        $this->loadSnippetGenerators($container);
        $this->loadDefaultClassGenerators($container);
        $this->loadDefaultContextReaders($container);
    }

    /**
     * Processes shared container after all extensions loaded.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->processContextInitializers($container);
        $this->processArgumentResolvers($container);
        $this->processContextReaders($container);
        $this->processClassGenerators($container);
        $this->processAnnotationReaders($container);
    }

    /**
     * Loads context environment handlers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEnvironmentHandlers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler');
        $definition->addTag(EnvironmentExtension::HANDLER_TAG, array('priority' => 50));
        $container->setDefinition(self::getEnvironmentHandlerId(), $definition);
    }

    /**
     * Loads context environment readers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEnvironmentReader(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\Environment\Reader\ContextEnvironmentReader');
        $definition->addTag(EnvironmentExtension::READER_TAG, array('priority' => 50));
        $container->setDefinition(self::getEnvironmentReaderId(), $definition);
    }

    /**
     * Loads context environment setup.
     *
     * @param ContainerBuilder $container
     */
    protected function loadSuiteSetup(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\Suite\Setup\SuiteWithContextsSetup', array(
            new Reference(AutoloaderExtension::CLASS_LOADER_ID),
            new Reference(FilesystemExtension::LOGGER_ID)
        ));
        $definition->addTag(SuiteExtension::SETUP_TAG, array('priority' => 20));
        $container->setDefinition(self::getSuiteSetupId(), $definition);
    }

    /**
     * Loads context snippet appender.
     *
     * @param ContainerBuilder $container
     */
    protected function loadSnippetAppender(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\Snippet\Appender\ContextSnippetAppender', array(
            new Reference(FilesystemExtension::LOGGER_ID)
        ));
        $definition->addTag(SnippetExtension::APPENDER_TAG, array('priority' => 50));
        $container->setDefinition(SnippetExtension::APPENDER_TAG . '.context', $definition);
    }

    /**
     * Loads context snippet generators.
     *
     * @param ContainerBuilder $container
     */
    protected function loadSnippetGenerators(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\Snippet\Generator\ContextTurnipSnippetGenerator');
        $definition->addTag(SnippetExtension::GENERATOR_TAG, array('priority' => 50));
        $container->setDefinition(SnippetExtension::GENERATOR_TAG . '.context.turnip', $definition);

        $definition = new Definition('Behat\Behat\Context\Snippet\Generator\ContextRegexSnippetGenerator');
        $definition->addTag(SnippetExtension::GENERATOR_TAG, array('priority' => 50));
        $container->setDefinition(SnippetExtension::GENERATOR_TAG . '.context.regex', $definition);
    }

    /**
     * Loads default context class generators.
     *
     * @param ContainerBuilder $container
     */
    protected function loadDefaultClassGenerators(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\ClassGenerator\SimpleContextClassGenerator');
        $definition->addTag(self::CLASS_GENERATOR_TAG, array('priority' => 50));
        $container->setDefinition(self::CLASS_GENERATOR_TAG . '.simple', $definition);
    }

    /**
     * Loads default context readers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadDefaultContextReaders(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\Reader\AnnotatedContextReader');
        $definition->addTag(self::READER_TAG, array('priority' => 50));
        $container->setDefinition(self::getAnnotatedContextReaderId(), $definition);

        $definition = new Definition('Behat\Behat\Context\Reader\TranslatableContextReader', array(
            new Reference(TranslatorExtension::TRANSLATOR_ID)
        ));
        $definition->addTag(self::READER_TAG, array('priority' => 50));
        $container->setDefinition(self::READER_TAG . '.translatable', $definition);
    }

    /**
     * Processes all context initializers.
     *
     * @param ContainerBuilder $container
     */
    protected function processContextInitializers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::INITIALIZER_TAG);
        $definition = $container->getDefinition(self::getEnvironmentHandlerId());

        foreach ($references as $reference) {
            $definition->addMethodCall('registerContextInitializer', array($reference));
        }
    }

    /**
     * Processes all argument resolvers.
     *
     * @param ContainerBuilder $container
     */
    protected function processArgumentResolvers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::ARGUMENT_RESOLVER_TAG);
        $definition = $container->getDefinition(self::getEnvironmentHandlerId());

        foreach ($references as $reference) {
            $definition->addMethodCall('registerArgumentResolver', array($reference));
        }
    }

    /**
     * Processes all context readers.
     *
     * @param ContainerBuilder $container
     */
    protected function processContextReaders(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::READER_TAG);
        $definition = $container->getDefinition(self::getEnvironmentReaderId());

        foreach ($references as $reference) {
            $definition->addMethodCall('registerContextReader', array($reference));
        }
    }

    /**
     * Processes all class generators.
     *
     * @param ContainerBuilder $container
     */
    protected function processClassGenerators(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::CLASS_GENERATOR_TAG);
        $definition = $container->getDefinition(self::getSuiteSetupId());

        foreach ($references as $reference) {
            $definition->addMethodCall('registerClassGenerator', array($reference));
        }
    }

    /**
     * Processes all annotation readers.
     *
     * @param ContainerBuilder $container
     */
    protected function processAnnotationReaders(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::ANNOTATION_READER_TAG);
        $definition = $container->getDefinition(self::getAnnotatedContextReaderId());

        foreach ($references as $reference) {
            $definition->addMethodCall('registerAnnotationReader', array($reference));
        }
    }

    /**
     * Returns context environment handler service id.
     *
     * @return string
     */
    private static function getEnvironmentHandlerId()
    {
        return EnvironmentExtension::HANDLER_TAG . '.context';
    }

    /**
     * Returns context environment reader id.
     *
     * @return string
     */
    private static function getEnvironmentReaderId()
    {
        return EnvironmentExtension::READER_TAG . '.context';
    }

    /**
     * Returns context suite setup id.
     *
     * @return string
     */
    private static function getSuiteSetupId()
    {
        return SuiteExtension::SETUP_TAG . '.suite_with_contexts';
    }

    /**
     * Returns annotated context reader id.
     *
     * @return string
     */
    private static function getAnnotatedContextReaderId()
    {
        return self::READER_TAG . '.annotated';
    }
}
