<?php

namespace Everzet\Behat\Environment;

use \Everzet\Behat\Environment\World;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * World container basic implementation.
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SimpleWorld implements World
{
  protected $file;
  protected $values = array();

  /**
   * Constructs World instance.
   *
   * @param     string  $file       file path to require on flushes
   */
  public function __construct($file = null)
  {
      $this->file = $file;
  }

  /**
   * @see   \Everzet\Behat\Environment\World
   */
  public function flush()
  {
      $this->values = array();

      if (null !== $this->file && is_file($this->file)) {
          require $this->file;
      }
  }

  /**
   * Sets a value in world.
   *
   * @param     string  $key        The unique identifier for service
   * @param     object  $service    The object to call
   */
  public function __set($key, $value)
  {
      $this->values[$key] = $value;
  }

  /**
   * Checks if value is set in current world.
   *
   * @param     string  $key        The unique identifier for service
   * 
   * @return    boolean             True if set
   */
  public function __isset($key)
  {
      return isset($this->values[$key]);
  }

  /**
   * Returns value by key.
   *
   * @param     string  $key        The unique identifier for service
   * 
   * @return    object
   */
  public function &__get($key)
  {
      return $this->values[$key];
  }
}