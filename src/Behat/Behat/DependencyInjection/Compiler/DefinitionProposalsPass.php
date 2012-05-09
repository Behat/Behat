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
 * Definition proposals pass - registers all available definition proposals.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionProposalsPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('behat.definition.proposal_dispatcher')) {
            return;
        }
        $dispatcher = $container->getDefinition('behat.definition.proposal_dispatcher');

        foreach ($container->findTaggedServiceIds('behat.definition.proposal') as $id => $attributes) {
            $dispatcher->addMethodCall('addProposal', array(new Reference($id)));
        }
    }
}
