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
 * Command pass - registers all available command processors.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CommandProcessorsPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerBuilder  $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('behat.processor.aggregate')) {
            return;
        }
        $aggregator = $container->getDefinition('behat.processor.aggregate');

        foreach ($container->findTaggedServiceIds('behat.processor') as $id => $attributes) {
            $aggregator->addMethodCall('addProcessor', array(new Reference($id)));
        }
    }
}
