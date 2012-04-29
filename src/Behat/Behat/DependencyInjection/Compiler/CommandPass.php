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
class CommandPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerBuilder  $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('behat.command.processors')) {
            return;
        }

        $processors = array();
        foreach ($container->findTaggedServiceIds('behat.processor') as $id => $attributes) {
            $processors[] = new Reference($id);
        }

        $container->setParameter('behat.command.processors', $processors);
    }
}
