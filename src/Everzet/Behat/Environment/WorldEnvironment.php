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
 * World container basic implementation.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class WorldEnvironment implements EnvironmentInterface
{
  protected $values = array();

  /**
   * @see   Everzet\Behat\Environment\EnvironmentInterface
   */
  public function loadEnvironmentFile($envFile)
  {
      if (is_file($envFile)) {
          $world = $this;
          require $envFile;
      }
  }

  /**
   * Print beautified debug string.
   *
   * @param     string  $string     debug string
   */
  public function printDebug($string)
  {
      echo "\n\033[36m|  " . strtr($string, array("\n" => "\n|  ")) . "\033[0m\n\n";
  }

  /**
   * Call previously saved in values closure.
   *
   * @param     string  $fn         function name
   * @param     array   $args       closure arguments
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
            E_USER_ERROR
        );
    }
  }

  /**
   * Set a value in world.
   *
   * @param     string  $key        The unique identifier for service
   * @param     object  $service    The object to call
   */
  public function __set($key, $value)
  {
      $this->values[$key] = $value;
  }

  /**
   * Check if value is set in current world.
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
   * Return value by key.
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
