<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Translator\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides translator service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TranslatorExtension implements Extension
{
    /*
     * Available services
     */
    public const TRANSLATOR_ID = 'translator';

    public function getConfigKey()
    {
        return 'translation';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $defaultLanguage = $this->getDefaultLanguage() ?: 'en';

        $childrenBuilder = $builder
            ->addDefaultsIfNotSet()
            ->children()
        ;
        $childrenBuilder
                ->scalarNode('locale')
                    ->info('Sets output locale for the tester')
                    ->defaultValue($defaultLanguage)
        ;
        $childrenBuilder
                ->scalarNode('fallback_locale')
                    ->info('Sets fallback output locale for the tester')
                    ->defaultValue('en')
        ;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadTranslator($container, $config['locale'], $config['fallback_locale']);
        $this->loadController($container);
    }

    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Loads translator service.
     *
     * @param string           $locale
     * @param string           $fallbackLocale
     */
    private function loadTranslator(ContainerBuilder $container, $locale, $fallbackLocale)
    {
        $definition = new Definition('Behat\Behat\Definition\Translator\Translator', [$locale]);
        $container->setDefinition(self::TRANSLATOR_ID, $definition);

        $definition->addMethodCall('setFallbackLocales', [[$fallbackLocale]]);
        $definition->addMethodCall(
            'addLoader',
            [
                'xliff',
                new Definition('Symfony\Component\Translation\Loader\XliffFileLoader'),
            ]
        );
        $definition->addMethodCall(
            'addLoader',
            [
                'yaml',
                new Definition('Symfony\Component\Translation\Loader\YamlFileLoader'),
            ]
        );
        $definition->addMethodCall(
            'addLoader',
            [
                'php',
                new Definition('Symfony\Component\Translation\Loader\PhpFileLoader'),
            ]
        );
        $definition->addMethodCall(
            'addLoader',
            [
                'array',
                new Definition('Symfony\Component\Translation\Loader\ArrayLoader'),
            ]
        );
        $container->setDefinition(self::TRANSLATOR_ID, $definition);
    }

    /**
     * Loads translator controller.
     */
    private function loadController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Translator\Cli\LanguageController', [
            new Reference(self::TRANSLATOR_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 800]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.translator', $definition);
    }

    /**
     * Tries to guess default user cli language.
     *
     * @return string|null
     */
    private function getDefaultLanguage()
    {
        $defaultLanguage = null;
        if (($locale = getenv('LANG')) && preg_match('/^([a-z]{2})/', $locale, $matches)) {
            $defaultLanguage = $matches[1];

            return $defaultLanguage;
        }

        return $defaultLanguage;
    }
}
