<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides additional service finding functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServiceProcessor
{
    /**
     * Finds and sorts (by priority) service references by provided tag.
     *
     * @param ContainerBuilder $container
     * @param string           $tag
     *
     * @return Reference[]
     */
    public function findAndSortTaggedServices(ContainerBuilder $container, $tag)
    {
        $serviceTags = array();
        foreach ($container->findTaggedServiceIds($tag) as $id => $tags) {
            $firstTags = current($tags);

            $serviceTags[] = array_merge(array('priority' => 0), $firstTags, array('id' => $id));
        }

        usort($serviceTags, function ($tag1, $tag2) { return $tag2['priority'] - $tag1['priority']; });
        $serviceReferences = array_map(function ($tag) { return new Reference($tag['id']); }, $serviceTags);

        return $serviceReferences;
    }

    /**
     * Processes wrappers of a service, found by provided tag.
     *
     * The wrappers are applied by descending priority.
     * The first argument of the wrapper service receives the inner service.
     *
     * @param ContainerBuilder $container
     * @param string           $target     The id of the service being decorated
     * @param string           $wrapperTag The tag used by wrappers
     */
    public function processWrapperServices(ContainerBuilder $container, $target, $wrapperTag)
    {
        $references = $this->findAndSortTaggedServices($container, $wrapperTag);

        foreach ($references as $reference) {
            $wrappedService = $container->getDefinition($target);
            $wrappingService = $container->getDefinition((string) $reference);
            $wrappingService->replaceArgument(0, $wrappedService);

            $container->setDefinition($target, $wrappingService);
        }
    }
}
