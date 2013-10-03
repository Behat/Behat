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
 * Context annotation readers pass.
 * Registers all available context annotation readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextAnnotationReadersPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $loader = $container->getDefinition('context.loader.annotated');

        foreach ($container->findTaggedServiceIds('context.annotation_reader') as $id => $attributes) {
            $loader->addMethodCall('registerAnnotationReader', array(new Reference($id)));
        }
    }
}
