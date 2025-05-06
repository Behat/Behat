<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\ServiceContainer;

use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides test hooking services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookExtension implements Extension
{
    /*
     * Available services
     */
    public const DISPATCHER_ID = 'hook.dispatcher';
    public const REPOSITORY_ID = 'hook.repository';

    public function getConfigKey()
    {
        return 'hooks';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadDispatcher($container);
        $this->loadRepository($container);
        $this->loadHookableTesters($container);
    }

    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Loads hook dispatcher.
     */
    protected function loadDispatcher(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Hook\HookDispatcher', [
            new Reference(self::REPOSITORY_ID),
            new Reference(CallExtension::CALL_CENTER_ID),
        ]);
        $container->setDefinition(self::DISPATCHER_ID, $definition);
    }

    /**
     * Loads hook repository.
     */
    protected function loadRepository(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Hook\HookRepository', [
            new Reference(EnvironmentExtension::MANAGER_ID),
        ]);
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Loads hookable testers.
     */
    protected function loadHookableTesters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Hook\Tester\HookableSuiteTester', [
            new Reference(TesterExtension::SUITE_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
        ]);
        $definition->addTag(TesterExtension::SUITE_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->setDefinition(TesterExtension::SUITE_TESTER_WRAPPER_TAG . '.hookable', $definition);
    }
}
