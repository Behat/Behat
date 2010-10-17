<?php

namespace Everzet\Behat\Hooks\Loader;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Plain PHP Files Hooks Loader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PHPLoader implements LoaderInterface
{
    protected $hooks = array();

    /**
     * Load hooks from file. 
     * 
     * @param   string          $path       plain php file path
     * @return  array                       array of hooks
     */
    public function load($path)
    {
        $hooks = $this;

        require_once($path);

        return $this->hooks;
    }

    /**
     * Hook to `*.before` event.
     *
     * @param   string  $context    context name (f.e. features.load => features.load.before event)
     * @param   string  $callback   callback
     */
    public function before($context, $callback)
    {
        $this->hooks[] = array($context . '.before', $callback);
    }

    /**
     * Hook to `*.after` event.
     *
     * @param   string  $context    context name (f.e. features.load => features.load.after event)
     * @param   string  $callback   callback
     */
    public function after($context, $callback)
    {
        $this->hooks[] = array($context . '.after', $callback);
    }
}

