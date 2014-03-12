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
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides translator service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TranslatorExtension extends BaseExtension
{
    /**
     * Loads translator controller.
     *
     * @param ContainerBuilder $container
     */
    protected function loadController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Translator\Cli\LanguageController', array(
            new Reference(self::TRANSLATOR_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 800));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.language', $definition);
    }
}
