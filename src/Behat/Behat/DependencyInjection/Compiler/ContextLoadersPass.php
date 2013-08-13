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
 * Context loaders pass.
 * Registers all available context loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextLoadersPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $readerDefinition = $container->getDefinition('context.callees_reader');

        foreach ($container->findTaggedServiceIds('context.loader') as $id => $attributes) {
            $readerDefinition->addMethodCall('registerLoader', array(new Reference($id)));
        }
    }
}
