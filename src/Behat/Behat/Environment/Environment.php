<?php

namespace Behat\Behat\Environment;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Environment.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Environment implements EnvironmentInterface
{
    /**
     * Saved values & closures.
     *
     * @var     array
     */
    protected $values = array();

    /**
     * {@inheritdoc}
     */
    public function loadEnvironmentResource($resource)
    {
        if (is_file($resource)) {
            $world = $this;
            require $resource;
        }
    }

    /**
     * Prints beautified debug string.
     *
     * @param     string  $string     debug string
     */
    public function printDebug($string)
    {
        echo "\n\033[36m|  " . strtr($string, array("\n" => "\n|  ")) . "\033[0m\n\n";
    }

    /**
     * Calls previously saved closure.
     *
     * @param     string  $name       function name
     * @param     array   $args       closure arguments
     *
     * @return    mixed
     */
    public function __call($name, array $args)
    {
      if (isset($this->values[$name]) && is_callable($this->values[$name])) {
          return call_user_func_array($this->values[$name], $args);
      } else {
          $trace = debug_backtrace();
          trigger_error(
              'Call to undefined method ' . get_class($this) . '::' . $name .
              ' in ' . $trace[0]['file'] .
              ' on line ' . $trace[0]['line'],
              E_USER_ERROR
          );
      }
    }

    /**
     * Sets a value in environment.
     *
     * @param     string  $key        The unique identifier for service
     * @param     object  $service    The object to call
     */
    public function __set($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Checks if value is set in current environment.
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
     * Returns a value by key.
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
