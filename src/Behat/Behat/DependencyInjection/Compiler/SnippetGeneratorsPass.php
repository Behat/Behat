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
 * Snippet generators pass.
 * Registers all available snippet generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetGeneratorsPass implements CompilerPassInterface
{
    /**
     * Processes container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $useCase = $container->getDefinition('snippet.use_case.create_snippet');

        foreach ($container->findTaggedServiceIds('snippet.generator') as $id => $attributes) {
            $useCase->addMethodCall('registerGenerator', array(new Reference($id)));
        }
    }
}
