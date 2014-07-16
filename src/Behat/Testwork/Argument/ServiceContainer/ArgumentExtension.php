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
 * Enables argument organises for the Testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ArgumentExtension implements Extension
{
    /*
     * Available services
     */
    const MIXED_ARGUMENT_ORGANISER_ID = 'argument.mixed_organiser';
    const PREG_MATCH_ARGUMENT_ORGANISER_ID = 'argument.preg_match_organiser';
    const CONSTRUCTOR_ARGUMENT_ORGANISER_ID = 'argument.constructor_organiser';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'argument';
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
        $definition = new Definition('Behat\Testwork\Argument\MixedArgumentOrganiser');
        $container->setDefinition(self::MIXED_ARGUMENT_ORGANISER_ID, $definition);

        $definition = new Definition('Behat\Testwork\Argument\PregMatchArgumentOrganiser', array(
            new Reference(self::MIXED_ARGUMENT_ORGANISER_ID)
        ));
        $container->setDefinition(self::PREG_MATCH_ARGUMENT_ORGANISER_ID, $definition);

        $definition = new Definition('Behat\Testwork\Argument\ConstructorArgumentOrganiser', array(
            new Reference(self::MIXED_ARGUMENT_ORGANISER_ID)
        ));
        $container->setDefinition(self::CONSTRUCTOR_ARGUMENT_ORGANISER_ID, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
