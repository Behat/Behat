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
use Symfony\Component\DependencyInjection\Definition;
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

        usort(
            $serviceTags,
            function ($tag1, $tag2) { return $tag2['priority'] - $tag1['priority']; }
        );
        $serviceReferences = array_map(
            function ($tag) { return new Reference($tag['id']); },
            $serviceTags
        );

        return $serviceReferences;
    }

    /**
     * Splits references based on the provided interface.
     *
     * References to services that implement interface go into the 1st collection, others into 2nd.
     *
     * @param ContainerBuilder $container
     * @param Reference[]      $references
     * @param string           $interface
     *
     * @return array
     */
    public function splitServices(ContainerBuilder $container, array $references, $interface)
    {
        $collection1 = array();
        $collection2 = array();

        foreach ($references as $reference) {
            $definition = $container->getDefinition($reference);
            if (is_subclass_of($definition->getClass(), $interface)) {
                $collection1[] = $reference;
            } else {
                $collection2[] = $reference;
            }
        }

        return array($collection1, $collection2);
    }

    /**
     * Processes wrappers of a service, found by provided tag.
     *
     * The wrappers are applied by descending priority.
     * The first argument of the wrapper service receives the inner service.
     *
     * @param ContainerBuilder $container
     * @param string           $target The id of the service being decorated
     * @param string           $wrapperTag The tag used by wrappers
     */
    public function processWrapperServices(ContainerBuilder $container, $target, $wrapperTag)
    {
        $wrapperReferences = $this->findAndSortTaggedServices($container, $wrapperTag);
        $this->wrapServiceInReferences($container, $target, $wrapperReferences);
    }

    /**
     * Wraps service with provided ID into stack of decorator references.
     *
     * @param ContainerBuilder $container
     * @param string           $target
     * @param Reference[]      $references
     */
    public function wrapServiceInReferences(
        ContainerBuilder $container,
        $target,
        array $references
    ) {
        foreach ($references as $reference) {
            $this->wrapService($container, $target, $reference);
        }
    }

    /**
     * Wraps service with ID in provided container with provided wrapper.
     *
     * @param ContainerBuilder $container
     * @param string           $serviceId
     * @param Reference        $wrapper
     */
    public function wrapService(ContainerBuilder $container, $serviceId, Reference $wrapper)
    {
        $id = (string)$wrapper;
        $renamedId = $id . '.inner';

        // This logic is based on Symfony\Component\DependencyInjection\Compiler\DecoratorServicePass

        // we create a new alias/service for the service we are replacing
        // to be able to reference it in the new one
        if ($container->hasAlias($serviceId)) {
            $alias = $container->getAlias($serviceId);
            $public = $alias->isPublic();
            $container->setAlias($renamedId, new Alias((string)$alias, false));
        } else {
            $definition = $container->getDefinition($serviceId);
            $public = $definition->isPublic();
            $definition->setPublic(false);
            $container->setDefinition($renamedId, $definition);
        }

        $container->setAlias($serviceId, new Alias($id, $public));
        // Replace the reference so that users don't need to bother about the way the inner service is referenced
        $wrappingService = $container->getDefinition($id);
        $wrappingService->replaceArgument(0, new Reference($renamedId));
    }

    /**
     * Wraps service with ID in provided container with provided wrapper.
     *
     * @param ContainerBuilder $container
     * @param string           $serviceId
     * @param string           $wrapperClass
     */
    public function wrapServiceInClass(ContainerBuilder $container, $serviceId, $wrapperClass)
    {
        $wrapperId = $serviceId . '.wrapper.' . md5($wrapperClass);
        $container->setDefinition($wrapperId, new Definition($wrapperClass, array(
            new Reference($serviceId)
        )));
        $this->wrapService($container, $serviceId, new Reference($wrapperId));
    }
}
