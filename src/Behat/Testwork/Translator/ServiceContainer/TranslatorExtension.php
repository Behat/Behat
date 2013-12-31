<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Translator\ServiceContainer;

use Behat\Behat\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Testwork translator extension.
 *
 * Provides translator service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TranslatorExtension implements Extension
{
    /*
     * Available services
     */
    const TRANSLATOR_ID = 'translator';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'translation';
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('locale')
                    ->defaultValue('en')
                ->end()
                ->scalarNode('fallback_locale')
                    ->defaultValue('en')
                ->end()
            ->end();
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadTranslator($container, $config['locale'], $config['fallback_locale']);
        $this->loadController($container);
    }

    /**
     * Processes shared container after all extensions loaded.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Loads translator service.
     *
     * @param ContainerBuilder $container
     * @param string           $locale
     * @param string           $fallbackLocale
     */
    protected function loadTranslator(ContainerBuilder $container, $locale, $fallbackLocale)
    {
        $definition = new Definition('Symfony\Component\Translation\Translator', array($locale));
        $container->setDefinition(self::TRANSLATOR_ID, $definition);

        $definition->addMethodCall('setFallbackLocale', array($fallbackLocale));
        $definition->addMethodCall(
            'addLoader', array(
                'xliff',
                new Definition('Symfony\Component\Translation\Loader\XliffFileLoader')
            )
        );
        $definition->addMethodCall(
            'addLoader', array(
                'yaml',
                new Definition('Symfony\Component\Translation\Loader\YamlFileLoader')
            )
        );
        $definition->addMethodCall(
            'addLoader', array(
                'php',
                new Definition('Symfony\Component\Translation\Loader\PhpFileLoader')
            )
        );
        $definition->addMethodCall(
            'addLoader', array(
                'array',
                new Definition('Symfony\Component\Translation\Loader\ArrayLoader')
            )
        );
        $container->setDefinition(self::TRANSLATOR_ID, $definition);
    }

    /**
     * Loads translator controller.
     *
     * @param ContainerBuilder $container
     */
    protected function loadController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Translator\Cli\LanguageController', array(
            new Reference(self::TRANSLATOR_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 800));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.translator', $definition);
    }
}
