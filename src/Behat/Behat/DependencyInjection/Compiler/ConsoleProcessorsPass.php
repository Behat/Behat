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
 * Console processors pass.
 * Registers all available console processors.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConsoleProcessorsPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $processors = array();
        foreach ($container->findTaggedServiceIds('console.processor') as $id => $attributes) {
            $processors[] = new Reference($id);
        }

        $container->getDefinition('console.command')->replaceArgument(0, $processors);
    }
}
