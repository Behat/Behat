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
 * Formatters pass.
 * Registers all available formatters.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutputFormattersPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $managerDefinition = $container->getDefinition('output.formatter_manager');

        $calls = array();
        foreach ($container->findTaggedServiceIds('output.formatter') as $id => $attributes) {
            $calls[] = array('registerFormatter', array(new Reference($id)));
        }

        $managerDefinition->setMethodCalls(array_merge($calls, $managerDefinition->getMethodCalls()));
    }
}
