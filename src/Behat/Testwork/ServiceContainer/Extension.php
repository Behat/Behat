<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Testwork extension interface.
 *
 * All testwork extensions should implement this interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Extension extends CompilerPassInterface
{
    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey();

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder);

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config);

    /**
     * Processes shared container after all extensions loaded.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container);
}
