<?php

namespace Behat\Behat\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    function load(array $config, ContainerBuilder $container);
}
