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
 * EventDispatcher pass - registers all available event subscribers.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcherPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerBuilder  $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('behat.event_dispatcher')) {
            return;
        }
        $dispatcherDefinition = $container->getDefinition('behat.event_dispatcher');

        foreach ($container->findTaggedServiceIds('behat.event_subscriber') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (isset($attribute['priority'])) {
                    $dispatcherDefinition->addMethodCall(
                        'addSubscriber', array(new Reference($id), $attribute['priority'])
                    );
                }
            }
        }
    }
}
