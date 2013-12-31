<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer;

use Behat\Testwork\ServiceContainer\Exception\ProcessingException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Testwork service processor.
 *
 * Provides additional service finding functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ServiceProcessor
{
    /**
     * Finds and sorts (by priority) service references by provided tag.
     *
     * @param ContainerBuilder $container
     * @param string           $tag
     *
     * @return Reference[]
     *
     * @throws ProcessingException
     */
    public function findAndSortTaggedServices(ContainerBuilder $container, $tag)
    {
        $serviceTags = array();
        foreach ($container->findTaggedServiceIds($tag) as $id => $tags) {
            $firstTags = current($tags);

            if (!isset($firstTags['priority'])) {
                throw new ProcessingException(sprintf(
                    'All `%s` tags should have a `priority` attribute, but `%s` service has none.',
                    $tag,
                    $id
                ));
            }

            $serviceTags[] = array_merge($firstTags, array('id' => $id));
        }

        usort($serviceTags, function ($tag1, $tag2) { return $tag2['priority'] - $tag1['priority']; });
        $serviceReferences = array_map(function ($tag) { return new Reference($tag['id']); }, $serviceTags);

        return $serviceReferences;
    }
}
