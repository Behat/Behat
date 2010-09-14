<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Event hooks loader
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HooksLoader
{
    protected $dispatcher;

    /**
     * Inits loader
     *
     * @param   string          $hooksFile  hook file to load from
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function __construct($hooksFile, EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        if (null !== $hooksFile && is_file($hooksFile)) {
            $hooks = $this;
            require $hooksFile;
        }
    }

    /**
     * Hook to fire before context
     *
     * @param   string  $context    context name (f.e. features.load => features.load.before event)
     * @param   string  $callback   callback
     */
    public function before($context, $callback)
    {
        $this->dispatcher->connect($context . '.before', $callback, 1);
    }

    /**
     * Hook to fire after context
     *
     * @param   string  $context    context name (f.e. features.load => features.load.after event)
     * @param   string  $callback   callback
     */
    public function after($context, $callback)
    {
        $this->dispatcher->connect($context . '.after', $callback, 1);
    }
}
