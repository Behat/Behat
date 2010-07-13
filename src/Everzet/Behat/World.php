<?php

namespace Everzet\Behat;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * World container.
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class World
{
    protected $file;
    protected $values = array();

    public function __construct($environmentFile = null)
    {
        if (null !== $environmentFile) {
            $this->file = $environmentFile;
        }
    }

    public function flush()
    {
        $this->values = array();

        if (null !== $this->file) {
            require $this->file;
        }
    }

    /**
     * Sets an object in world.
     *
     * @param   string  $key        The unique identifier for service
     * @param   object  $service    The object to call
     */
    public function __set($key, $value)
    {
        $this->values[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->values[$key]);
    }

    /**
     * Returns object by key
     *
     * @param   string  $key     The unique identifier for service
     * 
     * @return  object
     */
    public function &__get($key)
    {
        return $this->values[$key];
    }
}
