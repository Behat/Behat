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
 * Features loader pass.
 * Registers all available features loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeaturesLoadersPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $useCase = $container->getDefinition('features.use_case.load_features');

        foreach ($container->findTaggedServiceIds('features.loader') as $id => $attributes) {
            $useCase->addMethodCall('registerLoader', array(new Reference($id)));
        }
    }
}
