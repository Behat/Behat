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
      $world = $this;

      if (null !== $this->file && is_file($this->file)) {
          require $this->file;
      }
  }

  /**
   * Calls previously saved in values closure
   *
   * @param     string  $fn     function name
   * @param     array   $args   closure arguments
   * 
   * @return    mixed
   */
  public function __call($fn, array $args)
  {
    if (isset($this->values[$fn]) && is_callable($this->values[$fn])) {
        return call_user_func_array($this->values[$fn], $args);
    } else {
        $trace = debug_backtrace();
        trigger_error(
            'Call to undefined method ' . get_class($this) . '::' . $fn .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_FATAL_ERROR
        );
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