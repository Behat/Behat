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
 * Gherkin loaders pass.
 * Registers all available Gherkin loaders.
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
        $gherkinDefinition = $container->getDefinition('gherkin');

        foreach ($container->findTaggedServiceIds('gherkin.loader') as $id => $attributes) {
            $gherkinDefinition->addMethodCall('addLoader', array(new Reference($id)));
        }
    }
}
