<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Translator\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends translator service with knowledge about gherkin translations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GherkinTranslationsExtension implements Extension
{
    public function getConfigKey()
    {
        return 'gherkin_translations';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadController($container);
    }

    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Loads translator controller.
     */
    private function loadController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Translator\Cli\GherkinTranslationsController', [
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 9999]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.gherkin_translations', $definition);
    }
}
