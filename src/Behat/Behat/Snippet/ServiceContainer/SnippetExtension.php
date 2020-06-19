<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides snippet generation, printing and appending functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetExtension implements Extension
{
    /*
     * Available services
     */
    public const REGISTRY_ID = 'snippet.registry';
    public const WRITER_ID = 'snippet.writer';

    /*
     * Available extension points
     */
    public const GENERATOR_TAG = 'snippet.generator';
    public const APPENDER_TAG = 'snippet.appender';

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
        $this->processor = $processor ? : new ServiceProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'snippets';
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
        $this->loadController($container);
        $this->loadRegistry($container);
        $this->loadWriter($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processGenerators($container);
        $this->processAppenders($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function loadController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Snippet\Printer\ConsoleSnippetPrinter', array(
            new Reference(CliExtension::OUTPUT_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID)
        ));
        $container->setDefinition('snippet.printer', $definition);

        $definition = new Definition('Behat\Behat\Snippet\Cli\SnippetsController', array(
            new Reference(self::REGISTRY_ID),
            new Reference(self::WRITER_ID),
            new Reference('snippet.printer'),
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 400));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.snippet', $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function loadRegistry(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Snippet\SnippetRegistry');
        $container->setDefinition(self::REGISTRY_ID, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function loadWriter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Snippet\SnippetWriter');
        $container->setDefinition(self::WRITER_ID, $definition);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function processGenerators(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::GENERATOR_TAG);
        $definition = $container->getDefinition(self::REGISTRY_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSnippetGenerator', array($reference));
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function processAppenders(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::APPENDER_TAG);
        $definition = $container->getDefinition(self::WRITER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSnippetAppender', array($reference));
        }
    }
}
