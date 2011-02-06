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
 * Environment Builder.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EnvironmentBuilder
{
    protected $container;
    protected $files = array();

    /**
     * Initialize builder.
     * 
     * @param   ContainerInterface  $envClass   environment class
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Add Environment Config to builder.
     * 
     * @param   string  $file   file path
     */
    public function addResource($file)
    {
        $this->files[] = $file;
    }

    /**
     * Build & Initialize new Environment.
     * 
     * @return  EnvironmentInterface
     */
    public function build()
    {
        $environment = $this->container->get('behat.environment');

        foreach ($this->files as $file) {
            $environment->loadEnvironmentFile($file);
        }

        return $environment;
    }
}
