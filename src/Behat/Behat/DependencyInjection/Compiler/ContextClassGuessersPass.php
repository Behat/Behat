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
 * Context class guessers pass - registers all available context class guessers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextClassGuessersPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('behat.context_dispatcher')) {
            return;
        }
        $dispatcher = $container->getDefinition('behat.context_dispatcher');

        foreach ($container->findTaggedServiceIds('behat.context_class_guesser') as $id => $attributes) {
            $dispatcher->addMethodCall('addClassGuesser', array(new Reference($id)));
        }
    }
}
