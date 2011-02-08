<?php

namespace Behat\Behat\Environment;

use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Environment builder.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EnvironmentBuilder
{
    /**
     * Service container.
     *
     * @var     ContainerInterface
     */
    protected $container;
    /**
     * Environment resources.
     *
     * @var     array
     */
    protected $resources = array();

    /**
     * Initializes builder.
     *
     * @param   ContainerInterface  $envClass   environment class
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Adds environment resource (config).
     *
     * @param   string  $file   resource path
     */
    public function addResource($resource)
    {
        $this->resources[] = $resource;
    }

    /**
     * Returns newly created & configured environment.
     *
     * @return  Behat\Behat\Environment\EnvironmentInterface
     */
    public function build()
    {
        $environment = $this->container->get('behat.environment');

        foreach ($this->resources as $resource) {
            $environment->loadEnvironmentResource($resource);
        }

        return $environment;
    }
}
