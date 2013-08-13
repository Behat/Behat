<?php

namespace Behat\Behat\DependencyInjection\Compiler;

/*
 * This file is part of the Behat.
 *
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Suite generators pass.
 * Registers all available suite generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteGeneratorsPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $factoryDefinition = $container->getDefinition('suite.suite_factory');

        foreach ($container->findTaggedServiceIds('suite.generator') as $id => $attributes) {
            $factoryDefinition->addMethodCall('registerGenerator', array(new Reference($id)));
        }
    }
}
