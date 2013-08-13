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
 * Event subscribers pass.
 * Registers all available event subscribers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventSubscribersPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $dispatcherDefinition = $container->getDefinition('event_dispatcher');

        foreach ($container->findTaggedServiceIds('event_subscriber') as $id => $attributes) {
            $dispatcherDefinition->addMethodCall('addSubscriber', array(new Reference($id)));
        }
    }
}
