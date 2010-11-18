<?php

namespace Everzet\Behat\Environment;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
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
    protected $envClass;
    protected $files = array();

    /**
     * Initialize builder. 
     * 
     * @param   string  $envClass   environment class
     * @param   array   $files      array of enfironment files
     */
    public function __construct($envClass, array $files = array())
    {
        $this->envClass = $envClass;
        $this->files    = $files;
    }

    /**
     * Add Environment Config to builder. 
     * 
     * @param   string  $file   file path
     */
    public function addEnvironmentFile($file)
    {
        $this->files[] = $file;
    }

    /**
     * Build & Initialize new Environment. 
     * 
     * @return  EnvironmentInterface
     */
    public function buildEnvironment()
    {
        $class  = $this->envClass;
        $env    = new $class();

        foreach ($this->files as $file) {
            $env->loadEnvironmentFile($file);
        }

        return $env;
    }
}
