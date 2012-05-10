<?php

namespace Behat\Behat\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat extension interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    function load(array $config, ContainerBuilder $container);

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    function getConfig(ArrayNodeDefinition $builder);

    /**
     * Returns compiler passes used by this extension.
     *
     * @return array
     */
    function getCompilerPasses();
}
