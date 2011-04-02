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
class Environment extends \stdClass implements EnvironmentInterface
{
    /**
     * Environment parameters.
     *
     * @var     array
     */
    protected $parameters = array();

    /**
     * Sets environment parameter.
     *
     * @param   string  $name
     * @param   mixed   $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Returns environment parameter.
     *
     * @param   string  $name
     *
     * @return  mixed
     */
    public function getParameter($name)
    {
        if (!isset($this->parameters[$name])) {
            return null;
        }

        return $this->parameters[$name];
    }

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
     * Calls defined closure.
     *
     * @param     string  $name       function name
     * @param     array   $args       closure arguments
     *
     * @return    mixed
     */
    public function __call($name, array $args)
    {
        if (isset($this->$name) && is_callable($this->$name)) {
            return call_user_func_array($this->$name, $args);
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
}
