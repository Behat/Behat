<?php

namespace Behat\Behat\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/*
 * This file is part of the Behat.
 *
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Context loaders pass - registers all available context loaders.
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
        if (!$container->hasDefinition('behat.context.reader')) {
            return;
        }
        $readerDefinition = $container->getDefinition('behat.context.reader');

        foreach ($container->findTaggedServiceIds('behat.context.loader') as $id => $attributes) {
            $readerDefinition->addMethodCall('addLoader', array(new Reference($id)));
        }
    }
}
