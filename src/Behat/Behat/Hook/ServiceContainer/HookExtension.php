<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\Hook\Context\Attribute\HookAttributeReader;
use Behat\Behat\Hook\Tester\HookableFeatureTester;
use Behat\Behat\Hook\Tester\HookableScenarioTester;
use Behat\Behat\Hook\Tester\HookableStepTester;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Testwork\Hook\ServiceContainer\HookExtension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Testwork HookExtension with additional behat services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookExtension extends BaseExtension
{
    public function load(ContainerBuilder $container, array $config): void
    {
        parent::load($container, $config);

        $this->loadAttributeReader($container);
    }

    /**
     * Loads hookable testers.
     */
    protected function loadHookableTesters(ContainerBuilder $container): void
    {
        parent::loadHookableTesters($container);

        $definition = new Definition(HookableFeatureTester::class, [
            new Reference(TesterExtension::SPECIFICATION_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
        ]);
        $definition->addTag(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->setDefinition(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG . '.hookable', $definition);

        $definition = new Definition(
            HookableScenarioTester::class,
            [
                new Reference(TesterExtension::SCENARIO_TESTER_ID),
                new Reference(self::DISPATCHER_ID),
            ]
        );
        $definition->addTag(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->setDefinition(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG . '.hookable', $definition);

        $definition = new Definition(
            HookableScenarioTester::class,
            [
                new Reference(TesterExtension::EXAMPLE_TESTER_ID),
                new Reference(self::DISPATCHER_ID),
            ]
        );
        $definition->addTag(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->setDefinition(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG . '.hookable', $definition);

        $definition = new Definition(HookableStepTester::class, [
            new Reference(TesterExtension::STEP_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
        ]);
        $definition->addTag(TesterExtension::STEP_TESTER_WRAPPER_TAG, ['priority' => 9999]);
        $container->setDefinition(TesterExtension::STEP_TESTER_WRAPPER_TAG . '.hookable', $definition);
    }

    /**
     * Loads hook attribute reader.
     */
    private function loadAttributeReader(ContainerBuilder $container): void
    {
        $definition = new Definition(HookAttributeReader::class, [
            new Reference(DefinitionExtension::DOC_BLOCK_HELPER_ID),
        ]);
        $definition->addTag(ContextExtension::ATTRIBUTE_READER_TAG, ['priority' => 50]);
        $container->setDefinition(ContextExtension::ATTRIBUTE_READER_TAG . '.hook', $definition);
    }
}
