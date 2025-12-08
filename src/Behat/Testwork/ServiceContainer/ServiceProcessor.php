<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides additional service finding functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
final class ServiceProcessor
{
    /**
     * Finds and sorts (by priority) service references by provided tag.
     *
     * @param string           $tag
     *
     * @return list<Reference>
     */
    public function findAndSortTaggedServices(ContainerBuilder $container, $tag): array
    {
        $serviceTags = [];
        foreach ($container->findTaggedServiceIds($tag) as $id => $tags) {
            $firstTags = current($tags);

            $serviceTags[] = array_merge(['priority' => 0], $firstTags, ['id' => $id]);
        }

        usort($serviceTags, fn ($tag1, $tag2): int|float => $tag2['priority'] - $tag1['priority']);
        $serviceReferences = array_map(fn ($tag): Reference => new Reference($tag['id']), $serviceTags);

        return $serviceReferences;
    }

    /**
     * Processes wrappers of a service, found by provided tag.
     *
     * The wrappers are applied by descending priority.
     * The first argument of the wrapper service receives the inner service.
     *
     * @param string           $target     The id of the service being decorated
     * @param string           $wrapperTag The tag used by wrappers
     */
    public function processWrapperServices(ContainerBuilder $container, $target, $wrapperTag): void
    {
        $references = $this->findAndSortTaggedServices($container, $wrapperTag);

        foreach ($references as $reference) {
            $id = (string) $reference;
            $renamedId = $id . '.inner';

            // This logic is based on Symfony\Component\DependencyInjection\Compiler\DecoratorServicePass

            // we create a new alias/service for the service we are replacing
            // to be able to reference it in the new one
            if ($container->hasAlias($target)) {
                $alias = $container->getAlias($target);
                $public = $alias->isPublic();
                $container->setAlias($renamedId, new Alias((string) $alias, false));
            } else {
                $definition = $container->getDefinition($target);
                $public = $definition->isPublic();
                $definition->setPublic(false);
                $container->setDefinition($renamedId, $definition);
            }

            $container->setAlias($target, new Alias($id, $public));
            // Replace the reference so that users don't need to bother about the way the inner service is referenced
            $wrappingService = $container->getDefinition($id);
            $wrappingService->replaceArgument(0, new Reference($renamedId));
        }
    }
}
