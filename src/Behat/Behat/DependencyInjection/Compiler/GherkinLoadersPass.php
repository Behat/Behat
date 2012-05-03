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
 * Gherkin loaders pass - registers all available Gherkin loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinLoadersPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('gherkin')) {
            return;
        }
        $gherkinDefinition = $container->getDefinition('gherkin');

        foreach ($container->findTaggedServiceIds('gherkin.loader') as $id => $attributes) {
            $gherkinDefinition->addMethodCall('addLoader', array(new Reference($id)));
        }
    }
}
