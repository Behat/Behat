<?php

namespace Behat\Behat\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context dispatcher.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextDispatcher
{
    /**
     * Context class name.
     *
     * @var     string
     */
    private $contextClassName;
    /**
     * Context initialization parameters.
     *
     * @var     array
     */
    private $parameters = array();

    /**
     * Initialize dispatcher.
     *
     * @param   string  $contextClassName   context class name
     * @param   array   $parameters         context parameters
     */
    public function __construct($contextClassName, array $parameters = array())
    {
        $this->contextClassName = $contextClassName;
        $this->parameters       = $parameters;

        if (!class_exists($this->contextClassName)) {
            throw new \InvalidArgumentException(sprintf(
                'Class "%s" not found', $this->contextClassName
            ));
        }

        $contextClassRefl = new \ReflectionClass($this->contextClassName);
        if (!$contextClassRefl->implementsInterface('Behat\Behat\Context\ContextInterface')) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot use class "%s" as context, as it doesn\'t implement ContextInterface',
                $this->contextClassName
            ));
        }
    }

    /**
     * Create new context instance.
     *
     * @return  Behat\Behat\Context\ContextInterface
     */
    public function createContext()
    {
        return new $this->contextClassName($this->parameters);
    }
}
