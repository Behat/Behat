<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enables argument organisers for Testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ArgumentExtension implements Extension
{
    /*
     * Available services
     */
    public const MIXED_ARGUMENT_ORGANISER_ID = 'argument.mixed_organiser';
    public const PREG_MATCH_ARGUMENT_ORGANISER_ID = 'argument.preg_match_organiser';
    public const CONSTRUCTOR_ARGUMENT_ORGANISER_ID = 'argument.constructor_organiser';

    public function getConfigKey()
    {
        return 'argument';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('Behat\Testwork\Argument\MixedArgumentOrganiser');
        $container->setDefinition(self::MIXED_ARGUMENT_ORGANISER_ID, $definition);

        $definition = new Definition('Behat\Testwork\Argument\PregMatchArgumentOrganiser', [
            new Reference(self::MIXED_ARGUMENT_ORGANISER_ID),
        ]);
        $container->setDefinition(self::PREG_MATCH_ARGUMENT_ORGANISER_ID, $definition);

        $definition = new Definition('Behat\Testwork\Argument\ConstructorArgumentOrganiser', [
            new Reference(self::MIXED_ARGUMENT_ORGANISER_ID),
        ]);
        $container->setDefinition(self::CONSTRUCTOR_ARGUMENT_ORGANISER_ID, $definition);
    }

    public function process(ContainerBuilder $container)
    {
    }
}
